<?php

namespace App;

use App\Model\MigrationManagerModel;

require_once __DIR__ . '/../src/utils/functions.php';

class MigrationManager
{
    protected $model;
    public function __construct()
    {
        $this->model = new MigrationManagerModel();
        $this->createTableMigration();
        $this->playMigrations();
    }

    public function playMigrations()
    {
        $arrMigrationsExecuted = $this->getMigrationsExecuted();
        $arrMigrationFiles = glob(__DIR__ . '/../src/Database/Migrations/*.migration.php');

        sort($arrMigrationFiles);

        foreach ($arrMigrationFiles as $migratoinFile) {
            $nmMigration = basename($migratoinFile);

            if (in_array($nmMigration, $arrMigrationsExecuted)) {
                continue;
            }

            $nmClass = explode('.', explode('_', $nmMigration, 2)[1])[0];

            require_once $migratoinFile;
            $class = "App\\Database\\Migrations\\$nmClass";
            $migration = new $class();

            try {
                $this->transactionStart();
                $this->model->executeSqlMigration($migration->up());
                $this->model->insertMigration($nmMigration);
                $this->transactionCommit();
            } catch (\Exception $e) {
                $this->transactionRollback();
                http_response_code(500);
                throw new \Exception("Migration error $nmMigration: " . $e->getMessage(), 500);
            }
            unset($migration);
        }
    }


    public function getMigrationsExecuted(): array
    {
        $arrMigrationsExecuted = $this->model->getMigrationsExecuted();
        return $arrMigrationsExecuted;
    }

    public function createTableMigration(): void
    {
        $this->model->createTableMigration();
    }

    public function transactionStart()
    {
        $this->model->transactionStart();
    }

    public function transactionCommit()
    {
        $this->model->transactionCommit();
    }

    public function transactionRollback()
    {
        $this->model->transactionRollback();
    }
}
