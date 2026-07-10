<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/utils/functions.php';

use App\Database;
use App\Services\AstDescriptionService;

$service = new AstDescriptionService(getenv('OLLAMA_HOST') . '/api/');
$modelName = $service->getModelName();

$classes = Database::ExecuteSqlData(
    "SELECT * FROM astclasse WHERE DSDESCRIPTION IS NULL AND FLREVISADO = 'N' AND DSCODE IS NOT NULL"
);

echo "Gerando descrição para " . count($classes) . " classes...\n";

foreach ($classes as $classe) {
    try {
        $descricao = $service->generateClassDescription([
            'sistema' => $classe['NMSISTEMA'],
            'namespace' => $classe['NMNAMESPACE'],
            'name' => $classe['NMCLASS'],
            'type' => $classe['TPCLASS'],
            'parent' => $classe['NMPARENTCLASS'],
            'code' => $classe['DSCODE'],
        ]);

        $arrPdo = [
            ':DSDESCRIPTION' => $descricao,
            ':DSMODELOLLM' => $modelName,
            ':IDASTCLASS' => $classe['IDASTCLASS'],
        ];

        $arrUpdate = [
            'DSDESCRIPTION' => ':DSDESCRIPTION',
            'DSMODELOLLM' => ':DSMODELOLLM',
            'IDUSUARIOALT' => 0,
        ];

        Database::update('astclasse', $arrUpdate, 'IDASTCLASS = :IDASTCLASS', $arrPdo);

        echo "  ✓ {$classe['NMCLASS']}\n";
    } catch (\Exception $e) {
        error_log("Erro ao gerar descrição de {$classe['NMCLASS']}: " . $e->getMessage());
        echo "  ✗ {$classe['NMCLASS']} (falhou)\n";
    }
}

// --- Métodos sem descrição ---
$methods = Database::ExecuteSqlData(
    "SELECT pa.*, c.NMCLASS
     FROM astmethod pa
     JOIN astclasse c ON c.IDASTCLASS = pa.IDASTCLASS
     WHERE pa.DSDESCRIPTION IS NULL AND FLREVISADO = 'N' AND pa.DSCODE IS NOT NULL"
);

echo "Gerando descrição para " . count($methods) . " métodos...\n";

foreach ($methods as $method) {
    try {
        $descricao = $service->generateMethodDescription($method['NMCLASS'], [
            'name' => $method['NMMETHOD'],
            'code' => $method['DSCODE'],
        ]);

        $arrPdo = [
            ':DSDESCRIPTION' => $descricao,
            ':DSMODELOLLM' => $modelName,
            ':IDASTCLASS' => $method['IDASTMETHOD']
        ];

        $arrUpdate = [
            'DSDESCRIPTION' => ':DSDESCRIPTION',
            'DSMODELOLLM' => ':DSMODELOLLM',
            'IDUSUARIOALT' => 0,
        ];

        Database::update('astmethod', $arrUpdate, 'IDASTMETHOD = :IDASTCLASS', $arrPdo);

        echo "  ✓ {$method['NMCLASS']}::{$method['NMMETHOD']}\n";
    } catch (\Exception $e) {
        error_log("Erro ao gerar descrição de {$method['NMMETHOD']}: " . $e->getMessage());
        echo "  ✗ {$method['NMMETHOD']} (falhou)\n";
    }
}

// --- Funções soltas sem descrição ---
$functions = Database::ExecuteSqlData(
    "SELECT * FROM astfunction WHERE DSDESCRIPTION IS NULL AND AND FLREVISADO = 'N' DSCODE IS NOT NULL"
);

echo "Gerando descrição para " . count($functions) . " funções...\n";

foreach ($functions as $func) {
    try {
        $descricao = $service->generateFunctionDescription([
            'name' => $func['NMFUNCTION'],
            'code' => $func['DSCODE'],
        ]);

        $arrPdo = [
            ':DSDESCRIPTION' => $descricao,
            ':DSMODELOLLM' => $modelName,
            ':IDASTCLASS' => $func['IDASTFUNCTION']
        ];

        $arrUpdate = [
            'DSDESCRIPTION' => ':DSDESCRIPTION',
            'DSMODELOLLM' => ':DSMODELOLLM',
            'IDUSUARIOALT' => 0,
        ];

        Database::update('astfunction', $arrUpdate, 'IDASTFUNCTION = :IDASTCLASS', $arrPdo);

        echo "  ✓ {$func['NMFUNCTION']}\n";
    } catch (\Exception $e) {
        error_log("Erro ao gerar descrição de {$func['NMFUNCTION']}: " . $e->getMessage());
        echo "  ✗ {$func['NMFUNCTION']} (falhou)\n";
    }
}

echo "Concluído.\n";
