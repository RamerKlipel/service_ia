<?php

namespace App\Services;

use App\Models\AstParserServiceModel;

use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
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
        $classes = [];
        $files = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            ),
            '/\.php$/'
        );

        foreach ($files as $file) {
            $parsed = $this->parseFile($file->getPathname());
            $classes = array_merge($classes, $parsed);
        }

        return $classes;
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
                        'methods' => $methods,
                    ];
                }
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
                ];
            }

            public function getClasses(): array
            {
                return $this->classes;
            }
        };

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $visitor->getClasses();
    }
}
