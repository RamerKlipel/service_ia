<?php

namespace App\Models;

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
                FROM astclasses
                WHERE NMNAMESPACE = :NMNAMESPACE AND NMCLASS = :NMCLASS";
        $arrPdo = [
            ":NMNAMESPACE" => $classe['namespace'],
            ":NMCLASS" => $classe['name']
        ];

        $existing = Database::ExecuteSqlData($sql, $arrPdo);

        if ($existing && $existing['DSHASH'] === $classe['hash']) {
            // sem mudança, não mexe em FLREVISADO
            return (int) $existing['IDASTCLASS'];
        }

        if ($existing) {
            $arrPdo = [
                ":TPCLASS" => $classe['type'],
                ":NMPARENTCLASS" => $classe['parent'],
                ":DSINTERFACES" => json_encode($classe['interfaces']),
                ":DSFILEPATH" => $classe['file_path'],
                ":DSHASH" => $classe['hash'],
                ":IDASTCLASS" => $existing['IDASTCLASS'],
            ];

            $arrUpdate = [
                "TPCLASS" => ":TPCLASS,",
                "NMPARENTCLASS" => ":NMPARENTCLASS,",
                "DSINTERFACES" => ":DSINTERFACES,",
                "DSFILEPATH" => ":DSFILEPATH,",
                "DSHASH" => ":DSHASH,",
                "FLREVISADO" => "N",
            ];
            Database::update("astclasse", $arrUpdate, 'IDASTCLASS = :IDASTCLASS', $arrPdo);

            return (int) $existing['IDASTCLASS'];
        }

        $arrInsert = [
            "NMNAMESPACE" => ":NMNAMESPACE",
            "NMCLASS" => ":NMCLASS",
            "TPCLASS" => ":TPCLASS",
            "NMPARENTCLASS" => ":NMPARENTCLASS",
            "DSINTERFACES" => ":DSINTERFACES",
            "DSFILEPATH" => ":DSFILEPATH",
            "DSHASH" => ":DSHASH",
        ];

        $arrPdo = [
            ":NMNAMESPACE" => $classe['namespace'],,
            ":NMCLASS" => $classe['name'],,
            ":TPCLASS" => $classe['type'],,
            ":NMPARENTCLASS" => $classe['parent'],,
            ":DSINTERFACES" => json_encode($classe['interfaces']),,
            ":DSFILEPATH" => $classe['file_path'],,
            ":DSHASH" => $classe['hash'],,
        ];

        return (int)Database::insert("astclasse", $arrInsert, $arrPdo);
    }

    public function upsertMethod(int $idAstClass, array $method): void
    {
        $arrPdo = [
            ":IDASTCLASS" => $idAstClass,
            ":NMMETHOD" => $method['name'],
        ];
        $sql = "SELECT IDASTMETHOD, DSHASH FROM astmethods WHERE IDASTCLASS = :IDASTCLASS AND NMMETHOD = :NMMETHOD";
        $existing = Database::ExecuteSqlData($sql, $arrPdo);

        if ($existing && $existing['DSHASH'] === $method['hash']) {
            return; // sem mudança
        }

        if ($existing) {
            $arrPdo = [
                ':DSVISIBILITY' => $method['visibility'],
                ':DSPARAMS' => json_encode($method['params']),
                ':DSRETURNTYPE' => $method['return_type'],
                ':DSHASH' => $method['hash'],
                ':IDASTMETHOD' => $existing['IDASTMETHOD'],
            ];

            $arrUpdate = [
                "DSVISIBILITY" => ":vis",
                "DSPARAMS" => ":params",
                "DSRETURNTYPE" => ":return",
                "DSHASH" => ":hash",
                "FLREVISADO" => "N",
            ];
            Database::update("astmethod", $arrUpdate, 'IDASTMETHOD = :IDASTMETHOD', $arrPdo);
            return;
        }


        $arrInsert = [
            "IDASTCLASS" => ":IDASTCLASS",
            "NMMETHOD" => ":NMMETHOD",
            "DSVISIBILITY" => ":DSVISIBILITY",
            "DSPARAMS" => ":DSPARAMS",
            "DSRETURNTYPE" => ":DSRETURNTYPE",
            "DSHASH" => ":DSHASH",
        ];

        $arrPdo = [
            ':IDASTCLASS' => $idAstClass,
            ':NMMETHOD' => $method['name'],
            ':DSVISIBILITY' => $method['visibility'],
            ':DSPARAMS' => json_encode($method['params']),
            ':DSRETURNTYPE' => $method['return_type'],
            ':DSHASH' => $method['hash'],
        ];

        Database::insert("astmethod", $arrInsert, $arrPdo);
    }
}
