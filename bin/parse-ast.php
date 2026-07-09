<?php
// bin/parse-ast.php

require __DIR__ . '/../vendor/autoload.php';

use App\Services\AstParserService;

$targetPath = $argv[1] ?? null;

if (!$targetPath || !is_dir($targetPath)) {
    fwrite(STDERR, "Uso: php bin/parse-ast.php /caminho/do/codigo\n");
    exit(1);
}

$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s', getenv('DB_HOST'), getenv('DB_DATABASE')),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD')
);

$parser = new AstParserService();

$classes = $parser->scanDirectory($targetPath);

echo "Encontradas " . count($classes) . " classes.\n";

foreach ($classes as $classe) {
    $idAstClass = $parser->model->upsertClass($classe);
    echo "  {$classe['namespace']}\\{$classe['name']} → ID $idAstClass\n";

    foreach ($classe['methods'] as $method) {
        $parser->model->upsertMethod($idAstClass, $method);
    }
}

echo "Concluído.\n";
