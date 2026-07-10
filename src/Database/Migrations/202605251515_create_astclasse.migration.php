<?php

namespace App\Database\Migrations;

use App\Migration;

class create_astclasse extends Migration
{

    public function up(): string
    {
        $sql = "CREATE TABLE IF NOT EXISTS astclasse (
                    IDASTCLASS INT AUTO_INCREMENT PRIMARY KEY,
                    NMNAMESPACE VARCHAR(255) NOT NULL,
                    NMCLASS VARCHAR(255) NOT NULL,
                    TPCLASS ENUM('class', 'interface', 'trait', 'abstract') DEFAULT 'class',
                    NMPARENTCLASS VARCHAR(255) NULL,
                    DSINTERFACES TEXT NULL,
                    DSFILEPATH VARCHAR(500) NOT NULL,
                    DSHASH CHAR(64) NOT NULL,
                    DSCODE MEDIUMTEXT NULL,
                    DSDESCRIPTION TEXT NULL,
                    NMSISTEMA VARCHAR(100) NOT NULL,
                    TPARQUIVO ENUM('controller','model','view','base') NOT NULL,
                    NMEXTENDS VARCHAR(100),
                    NMARQUIVO VARCHAR(255),
                    JSONMETODOS JSON NULL,
                    DSCLASSELLM TEXT,
                    FLREVISADO ENUM('S','N') DEFAULT 'N',
                    FLENVIADOGRAPHRAG ENUM('S','N') DEFAULT 'N',
                    FLENVIADOQDRANT ENUM('S','N') DEFAULT 'N',
                    IDUSUARIOINC INT NOT NULL,
                    DTINCLUSAO DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    IDUSUARIOALT INT NOT NULL,
                    DTALTERACAO DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uk_class (NMSISTEMA, NMNAMESPACE, NMCLASS)
                )";
        return $sql;
    }

    public function down(): string
    {
        $sql = "DROP TABLE IF EXISTS astclasse";
        return $sql;
    }
}
