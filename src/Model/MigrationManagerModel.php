<?php

namespace App\Model;

use App\Database;
use App\Model;

class MigrationManagerModel extends Model
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

    public function createTableMigration(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migration (
                    IDMIGRATION INT NOT NULL AUTO_INCREMENT,
                    NMMIGRATION VARCHAR(50) NOT NULL,
                    DAEXECUTED TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (IDMIGRATION)
                );";
        Database::ExecuteSql($sql);
    }

    public function executeSqlMigration(string $sql): string|int
    {
        return Database::ExecuteSql($sql);
    }

    public function insertMigration(string $nmMigration)
    {
        Database::insert('migration', ['NMMIGRATION' => ':NMMIGRATION'], [":NMMIGRATION" => $nmMigration]);
    }
}
