<?php

namespace App\Database\Migrations;

use App\Migration;

class create_astfunction extends Migration
{

    public function up(): string
    {
        $sql = "CREATE TABLE astfunction (
                    IDASTFUNCTION INT AUTO_INCREMENT PRIMARY KEY,
                    NMNAMESPACE VARCHAR(255) NULL,
                    NMFUNCTION VARCHAR(255) NOT NULL,
                    NMSISTEMA VARCHAR(100) NOT NULL,
                    DSPARAMS TEXT NULL,
                    DSRETURNTYPE VARCHAR(100) NULL,
                    JSONMETODOS JSON NULL,
                    DSFILEPATH VARCHAR(500) NOT NULL,
                    DSHASH CHAR(64) NOT NULL,
                    DSCODE MEDIUMTEXT NULL,
                    DSDESCRIPTION TEXT NULL,
                    DSMODELOLLM VARCHAR(100) NULL,
                    FLREVISADO ENUM ('S', 'N') DEFAULT 'N',
                    FLENVIADOGRAPHRAG ENUM ('S', 'N') DEFAULT 'N',
                    FLENVIADOQDRANT ENUM ('S', 'N') DEFAULT 'N',
                    DTINCLUSAO TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    DTALTERACAO TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uk_function (NMSISTEMA, NMNAMESPACE, NMFUNCTION)
                )";
        return $sql;
    }

    public function down(): string
    {
        $sql = "DROP TABLE IF EXISTS astmethods";
        return $sql;
    }
}
