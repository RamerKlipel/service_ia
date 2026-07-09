<?php

namespace App\Model;

use App\Database;
use App\Model;

class OllamaChatServiceModel extends Model
{
    public function getMigrationsExecuted(): array
    {
        $sql = 'SELECT IDMIGRATION, NMMIGRATION, DAEXECUTED
                FROM migration';
        $arrMigrationsExecuted = Database::ExecuteSqlData($sql);
        $arrMigrations = [];
        foreach ($arrMigrationsExecuted as $arrDataMigrations) {
            $arrMigrations[] = $arrDataMigrations['NMMIGRATION'];
        }
        return $arrMigrations;
    }

}
