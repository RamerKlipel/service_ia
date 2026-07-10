<?php

namespace App\Services;

use App\Model\AstParserServiceModel;

use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\PrettyPrinter\Standard;

class AstParserService
{
    private $parser;
    public ?AstParserServiceModel $model = null;
    private Standard $printer;

    public function __construct()
    {
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
        $this->printer = new Standard();
        $this->model = new AstParserServiceModel;
    }

    /**
     * Varre um diretório inteiro e retorna um array de classes parseadas.
     */
    public function scanDirectory(string $path): array
    {
        $allClasses = [];
        $allFunctions = [];
        $files = new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)), '/\.php$/');

        foreach ($files as $file) {
            try {
                $parsed = $this->parseFile($file->getPathname());
                $allClasses = array_merge($allClasses, $parsed["classes"]);
                $allFunctions = array_merge($allFunctions, $parsed["functions"]);
            } catch (\UnexpectedValueException $e) {
                error_log("Pulando {$file->getPathname()}: " . $e->getMessage());
                continue;
            }
        }

        return [
            "classes" => $allClasses,
            "functions" => $allFunctions
        ];
    }

    /**
     * Parseia um único arquivo PHP e extrai suas classes/interfaces/traits.
     */
    public function parseFile(string $filePath): array
    {
        $code = file_get_contents($filePath);

        try {
            $ast = $this->parser->parse($code);
        } catch (\PhpParser\Error $e) {
            error_log("Erro ao parsear $filePath: " . $e->getMessage());
            return [];
        }

        $visitor = new class($filePath, $this->printer) extends NodeVisitorAbstract {
            private string $currentNamespace = '';
            private array $classes = [];
            private array $functions = [];
            private string $filePath;
            private Standard $printer;

            public function __construct(string $filePath, Standard $printer)
            {
                $this->filePath = $filePath;
                $this->printer = $printer;
            }

            public function enterNode(Node $node)
            {
                if ($node instanceof Namespace_) {
                    $this->currentNamespace = $node->name ? $node->name->toString() : '';
                }

                if ($node instanceof Class_ || $node instanceof Interface_ || $node instanceof Trait_) {
                    if ($node->name == null) {
                        return;
                    }

                    $type = match (true) {
                        $node instanceof Interface_ => 'interface',
                        $node instanceof Trait_ => 'trait',
                        $node instanceof Class_ && $node->isAbstract() => 'abstract',
                        default => 'class',
                    };

                    $parentClass = null;
                    $interfaces = [];

                    if ($node instanceof Class_) {
                        $parentClass = $node->extends ? $node->extends->toString() : null;
                        foreach ($node->implements as $impl) {
                            $interfaces[] = $impl->toString();
                        }
                    } elseif ($node instanceof Interface_) {
                        foreach ($node->extends as $ext) {
                            $interfaces[] = $ext->toString();
                        }
                    }

                    $methods = [];
                    foreach ($node->getMethods() as $method) {
                        $methods[] = $this->extractMethod($method);
                    }

                    $classCode = $this->printer->prettyPrint([$node]);

                    $this->classes[] = [
                        'namespace' => $this->currentNamespace,
                        'name' => $node->name->toString(),
                        'type' => $type,
                        'parent' => $parentClass,
                        'interfaces' => $interfaces,
                        'file_path' => $this->filePath,
                        'hash' => hash('sha256', $classCode),
                        'code' => $classCode,
                        'methods' => $methods,
                    ];
                }

                if ($node instanceof Function_) {
                    $this->functions[] = $this->extractFunction($node);
                }
            }

            private function extractFunction(Function_ $node): array
            {
                $params = [];
                foreach ($node->params as $param) {
                    $params[] = [
                        'name' => $param->var->name ?? '',
                        'type' => $param->type ? $this->printer->prettyPrint([$param->type]) : null,
                    ];
                }

                $funcCode = $this->printer->prettyPrint([$node]);

                return [
                    'namespace' => $this->currentNamespace ?: null,
                    'name' => $node->name->toString(),
                    'params' => $params,
                    'return_type' => $node->returnType ? $this->printer->prettyPrint([$node->returnType]) : null,
                    'file_path' => $this->filePath,
                    'hash' => hash('sha256', $funcCode),
                    'code' => $funcCode,
                    'calls' => $this->extractCalls($node),
                ];
            }

            private function extractMethod(ClassMethod $method): array
            {
                $params = [];
                foreach ($method->params as $param) {
                    $params[] = [
                        'name' => $param->var->name ?? '',
                        'type' => $param->type ? $this->printer->prettyPrint([$param->type]) : null,
                    ];
                }

                $visibility = match (true) {
                    $method->isPrivate() => 'private',
                    $method->isProtected() => 'protected',
                    default => 'public',
                };

                $methodCode = $this->printer->prettyPrint([$method]);

                return [
                    'name' => $method->name->toString(),
                    'visibility' => $visibility,
                    'params' => $params,
                    'return_type' => $method->returnType ? $this->printer->prettyPrint([$method->returnType]) : null,
                    'hash' => hash('sha256', $methodCode),
                    'code' => $methodCode,
                    'calls' => $this->extractCalls($method),
                ];
            }

            private function extractCalls(Node $node): array
            {
                $finder = new NodeFinder();
                $calls = [];

                foreach ($finder->findInstanceOf($node, Node\Expr\MethodCall::class) as $call) {
                    if ($call->name instanceof Node\Identifier) {
                        $calls[] = ['type' => 'method', 'name' => $call->name->toString()];
                    }
                }

                foreach ($finder->findInstanceOf($node, Node\Expr\StaticCall::class) as $call) {
                    if ($call->name instanceof Node\Identifier) {
                        $calls[] = [
                            'type' => 'static',
                            'class' => $call->class instanceof Node\Name ? $call->class->toString() : null,
                            'name' => $call->name->toString(),
                        ];
                    }
                }

                foreach ($finder->findInstanceOf($node, Node\Expr\FuncCall::class) as $call) {
                    if ($call->name instanceof Node\Name) {
                        $calls[] = ['type' => 'function', 'name' => $call->name->toString()];
                    }
                }

                foreach ($finder->findInstanceOf($node, Node\Expr\New_::class) as $new) {
                    if ($new->class instanceof Node\Name) {
                        $calls[] = ['type' => 'instantiation', 'name' => $new->class->toString()];
                    }
                }

                return $calls;
            }

            public function getClasses(): array
            {
                return $this->classes;
            }

            public function getFunctions(): array
            {
                return $this->functions;
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return [
            "classes" => $visitor->getClasses(),
            "functions" => $visitor->getFunctions()
        ];
    }
}
