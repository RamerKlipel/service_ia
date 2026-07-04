<?php

namespace App\Database\Migrations;

use App\Migration;

class create_astclasses extends Migration
{

    public function up(): string
    {
        $sql = "CREATE TABLE IF NOT EXISTS astclasses (
                    IDASTCLASSES INT AUTO_INCREMENT PRIMARY KEY,
                    NMSISTEMA VARCHAR(100) NOT NULL,
                    TPARQUIVO ENUM('controller','model','view','base') NOT NULL,
                    NMCLASSE VARCHAR(100) NOT NULL,
                    NMEXTENDS VARCHAR(100),
                    NMARQUIVO VARCHAR(255),
                    JSONMETODOS JSON,
                    DSCLASSELLM TEXT,
                    FLREVISADO ENUM('S','N') DEFAULT 'N',
                    FLENVIADOGRAPHRAG ENUM('S','N') DEFAULT 'N',
                    FLENVIADOQDRANT ENUM('S','N') DEFAULT 'N',
                    IDUSUARIOINC INT NOT NULL,
                    DTINCLUSAO DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    IDUSUARIOALT INT NOT NULL,
                    DTALTERACAO DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                )";
        return $sql;
    }

    public function down(): string
    {
        $sql = "DROP TABLE IF EXISTS astclasses";
        return $sql;
    }
}
