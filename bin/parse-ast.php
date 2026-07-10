<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/utils/functions.php';

use App\Services\AstParserService;

$targetPath = $argv[1] ?? null;
$nmSistema = $argv[2] ?? null;

if (!$targetPath || !is_dir($targetPath) || !$nmSistema) {
    fwrite(STDERR, "Uso: php bin/parse-ast.php /caminho/do/codigo  NOME_SISTEMA\n");
    exit(1);
}

$parser = new AstParserService();

$result = $parser->scanDirectory($targetPath);

echo "Encontradas " . count($result["classes"]) . " classes.\n";
foreach ($result["classes"] as $classe) {
    $classe["nm_sistema"] = $nmSistema;
    $idAstClass = $parser->model->upsertClass($classe);
    echo "  {$classe['namespace']}\\{$classe['name']} → ID $idAstClass\n";

    foreach ($classe['methods'] as $method) {
        $parser->model->upsertMethod($idAstClass, $method);
    }
}

echo "Encontradas " . count($result['functions']) . " funções soltas.\n";
foreach ($result['functions'] as $func) {
    $func["nm_sistema"] = $nmSistema;
    $idFunc = $parser->model->upsertFunction($func);
    echo "  {$func['name']}() → ID $idFunc\n";
}

echo "Concluído.\n";
