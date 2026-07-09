<?php

namespace App;

use App\Database;

abstract class Model
{

    public function ExecuteSql(string $sql): string|int
    {
        return Database::ExecuteSql($sql);
    }

    public function transactionStart(): void
    {
        Database::transactionStart();
    }

    public function transactionCommit(): void
    {
        Database::transactionCommit();
    }

    public function transactionRollback(): void
    {
        Database::transactionRollback();
    }

    public function executeSqlMountAssociativeArray(string $sql, string $index, string $val, array $arrPdo = []): array
    {
        return Database::executeSqlMountAssociativeArray($sql, $index, $val, $arrPdo);
    }
}
