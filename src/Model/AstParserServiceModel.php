<?php

namespace App\Model;

use App\Database;
use App\Model;

class AstParserServiceModel extends Model
{

    /**
     * Insere ou atualiza a classe. Só reseta FLREVISADO se o hash mudou.
     * Retorna o IDASTCLASS.
     */
    public function upsertClass(array $classe): int
    {
        $sql = "SELECT IDASTCLASS, DSHASH
                FROM astclasse
                WHERE NMNAMESPACE = :NMNAMESPACE AND NMCLASS = :NMCLASS AND NMSISTEMA = :NMSISTEMA";

        $arrPdo = [
            ":NMNAMESPACE" => $classe["namespace"],
            ":NMCLASS" => $classe["name"],
            ":NMSISTEMA" => $classe["nm_sistema"],
        ];

        $existing = Database::ExecuteSqlData($sql, $arrPdo);

        if ($existing && $existing["DSHASH"] === $classe["hash"]) {
            // sem mudança, não mexe em FLREVISADO
            return (int) $existing["IDASTCLASS"];
        }

        $jsonMetodos = json_encode(array_map(fn($m) => [
            "name" => $m["name"],
            "calls" => $m["calls"],
        ], $classe["methods"]));

        if ($existing) {
            $arrPdo = [
                ":TPCLASS" => $classe["type"],
                ":NMPARENTCLASS" => $classe["parent"],
                ":DSINTERFACES" => json_encode($classe["interfaces"]),
                ":DSFILEPATH" => $classe["file_path"],
                ":DSHASH" => $classe["hash"],
                ":IDASTCLASS" => $existing["IDASTCLASS"],
                ":NMSISTEMA" => $classe["nm_sistema"],
                ":IDUSUARIOINC" => 0,
                ":IDUSUARIOALT" => 0,
                ":JSONMETODOS" => $jsonMetodos,
                ":DSCODE" => $classe["code"],
            ];

            $arrUpdate = [
                "TPCLASS" => ":TPCLASS,",
                "NMPARENTCLASS" => ":NMPARENTCLASS,",
                "DSINTERFACES" => ":DSINTERFACES,",
                "DSFILEPATH" => ":DSFILEPATH,",
                "DSHASH" => ":DSHASH,",
                "FLREVISADO" => "N",
                "IDUSUARIOINC" => ":IDUSUARIOINC",
                "IDUSUARIOALT" => ":IDUSUARIOALT",
                "JSONMETODOS" => ":JSONMETODOS",
                "DSCODE" => ":DSCODE",
            ];
            Database::update("astclasse", $arrUpdate, "IDASTCLASS = :IDASTCLASS AND NMSISTEMA = :NMSISTEMA", $arrPdo);

            return (int) $existing["IDASTCLASS"];
        }

        $arrInsert = [
            "NMNAMESPACE" => ":NMNAMESPACE",
            "NMCLASS" => ":NMCLASS",
            "TPCLASS" => ":TPCLASS",
            "NMPARENTCLASS" => ":NMPARENTCLASS",
            "DSINTERFACES" => ":DSINTERFACES",
            "DSFILEPATH" => ":DSFILEPATH",
            "DSHASH" => ":DSHASH",
            "NMSISTEMA" => ":NMSISTEMA",
            "IDUSUARIOINC" => ":IDUSUARIOINC",
            "IDUSUARIOALT" => ":IDUSUARIOALT",
            "JSONMETODOS" => ":JSONMETODOS",
            "DSCODE" => ":DSCODE",
        ];

        $arrPdo = [
            ":NMNAMESPACE" => $classe["namespace"],
            ":NMCLASS" => $classe["name"],
            ":TPCLASS" => $classe["type"],
            ":NMPARENTCLASS" => $classe["parent"],
            ":DSINTERFACES" => json_encode($classe["interfaces"]),
            ":DSFILEPATH" => $classe["file_path"],
            ":DSHASH" => $classe["hash"],
            ":NMSISTEMA" => $classe["nm_sistema"],
            ":IDUSUARIOINC" => 0,
            ":IDUSUARIOALT" => 0,
            ":JSONMETODOS" => $jsonMetodos,
            ":DSCODE" => $classe["code"],
        ];

        return (int)Database::insert("astclasse", $arrInsert, $arrPdo);
    }

    public function upsertMethod(int $idAstClass, array $method): void
    {
        $arrPdo = [
            ":IDASTCLASS" => $idAstClass,
            ":NMMETHOD" => $method["name"],
        ];
        $sql = "SELECT IDASTMETHOD, DSHASH FROM astmethod WHERE IDASTCLASS = :IDASTCLASS AND NMMETHOD = :NMMETHOD";
        $existing = Database::ExecuteSqlData($sql, $arrPdo);

        if ($existing && $existing["DSHASH"] === $method["hash"]) {
            return; // sem mudança
        }

        if ($existing) {
            $arrPdo = [
                ":DSVISIBILITY" => $method["visibility"],
                ":DSPARAMS" => json_encode($method["params"]),
                ":DSRETURNTYPE" => $method["return_type"],
                ":DSHASH" => $method["hash"],
                ":IDASTMETHOD" => $existing["IDASTMETHOD"],
                ":IDUSUARIOINC" => 0,
                ":IDUSUARIOALT" => 0,
                ":JSONMETODOS" => json_encode($method["calls"]),
                ":DSCODE" => $method["code"],
            ];

            $arrUpdate = [
                "DSVISIBILITY" => ":DSVISIBILITY",
                "DSPARAMS" => ":DSPARAMS",
                "DSRETURNTYPE" => ":DSRETURNTYPE",
                "DSHASH" => ":DSHASH",
                "JSONMETODOS" => ":JSONMETODOS",
                "DSCODE" => ":DSCODE",
            ];
            Database::update("astmethod", $arrUpdate, "IDASTMETHOD = :IDASTMETHOD", $arrPdo);
            return;
        }

        $arrInsert = [
            "IDASTCLASS" => ":IDASTCLASS",
            "NMMETHOD" => ":NMMETHOD",
            "DSVISIBILITY" => ":DSVISIBILITY",
            "DSPARAMS" => ":DSPARAMS",
            "DSRETURNTYPE" => ":DSRETURNTYPE",
            "DSHASH" => ":DSHASH",
            "IDUSUARIOINC" => ":IDUSUARIOINC",
            "IDUSUARIOALT" => ":IDUSUARIOALT",
            "JSONMETODOS" => ":JSONMETODOS",
            "DSCODE" => ":DSCODE",
        ];

        $arrPdo = [
            ":IDASTCLASS" => $idAstClass,
            ":NMMETHOD" => $method["name"],
            ":DSVISIBILITY" => $method["visibility"],
            ":DSPARAMS" => json_encode($method["params"]),
            ":DSRETURNTYPE" => $method["return_type"],
            ":DSHASH" => $method["hash"],
            ":IDUSUARIOINC" => 0,
            ":IDUSUARIOALT" => 0,
            ":JSONMETODOS" => json_encode($method["calls"]),
            ":DSCODE" => json_encode($method["code"]),
        ];

        Database::insert("astmethod", $arrInsert, $arrPdo);
    }

    public function upsertFunction(array $func): int
    {
        $arrPdo = [
            ":NMFUNCTION" => $func["name"],
            ":DSFILEPATH" => $func["file_path"],
            ":NMNAMESPACE" => $func["namespace"],
            ":NMSISTEMA" => $func["nm_sistema"],
        ];

        $sql = "SELECT IDASTFUNCTION, DSHASH
                FROM astfunction
                WHERE NMFUNCTION = :NMFUNCTION AND DSFILEPATH = :DSFILEPATH AND NMSISTEMA = :NMSISTEMA
                AND (NMNAMESPACE = :NMNAMESPACE OR (NMNAMESPACE IS NULL AND :NMNAMESPACE IS NULL))";

        $existing = Database::ExecuteSqlData($sql, $arrPdo);

        if ($existing && $existing["DSHASH"] === $func["hash"]) {
            return (int) $existing["IDASTFUNCTION"];
        }

        if ($existing) {
            $arrPdo = [
                ":params" => json_encode($func["params"]),
                ":return" => $func["return_type"],
                ":hash" => $func["hash"],
                ":id" => $existing["IDASTFUNCTION"],
                ":IDUSUARIOINC" => 0,
                ":IDUSUARIOALT" => 0,
                ":JSONMETODOS" => json_encode($func["calls"]),
                "DSCODEJSONMETODOS" => json_encode($func["DSCODE"]),

            ];

            $arrUpdate = [
                "DSPARAMS" => ":params",
                "DSRETURNTYPE" => ":return",
                "DSHASH" => ":hash",
                "IDUSUARIOINC" => ":IDUSUARIOINC",
                "IDUSUARIOALT" => ":IDUSUARIOALT",
                "JSONMETODOS" => ":JSONMETODOS",
                "DSCODE" => ":DSCODE",
            ];

            return (int)Database::update("astfunction", $arrUpdate, "IDASTFUNCTION = :id", $arrPdo);
        }

        $arrInsert = [
            "NMNAMESPACE" => ":NMNAMESPACE",
            "NMFUNCTION" => ":NMFUNCTION",
            "DSPARAMS" => ":DSPARAMS",
            "DSRETURNTYPE" => ":DSRETURNTYPE",
            "DSFILEPATH" => ":DSFILEPATH",
            "DSHASH" => ":DSHASH",
            "IDUSUARIOINC" => ":IDUSUARIOINC",
            "IDUSUARIOALT" => ":IDUSUARIOALT",
            "JSONMETODOS" => ":JSONMETODOS",
            "DSCODE" => ":DSCODE",
        ];

        $arrPdo = [
            ":NMNAMESPACE" => $func["namespace"],
            ":NMFUNCTION" => $func["name"],
            ":DSPARAMS" => json_encode($func["params"]),
            ":DSRETURNTYPE" => $func["return_type"],
            ":DSFILEPATH" => $func["file_path"],
            ":DSHASH" => $func["hash"],
            ":IDUSUARIOINC" => 0,
            ":IDUSUARIOALT" => 0,
            ":JSONMETODOS" => json_encode($func["calls"]),
            "DSCODEJSONMETODOS" => json_encode($func["DSCODE"]),
        ];

        return (int) Database::insert("astfunction", $arrInsert, $arrPdo);
    }
}
