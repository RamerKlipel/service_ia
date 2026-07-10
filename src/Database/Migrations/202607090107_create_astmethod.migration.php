<?php

namespace App\Database\Migrations;

use App\Migration;

class create_astmethod extends Migration
{

    public function up(): string
    {
        $sql = "CREATE TABLE IF NOT EXISTS astmethod (
                    IDASTMETHOD INT AUTO_INCREMENT PRIMARY KEY,
                    IDASTCLASS INT NOT NULL,
                    NMMETHOD VARCHAR(255) NOT NULL,
                    DSVISIBILITY ENUM('public', 'protected', 'private') DEFAULT 'public',
                    DSPARAMS TEXT NULL,
                    DSRETURNTYPE VARCHAR(100) NULL,
                    JSONMETODOS JSON NULL,
                    DSHASH CHAR(64) NOT NULL,
                    DSCODE MEDIUMTEXT NULL,
                    DSDESCRIPTION TEXT NULL,
                    FLREVISADO ENUM('S', 'N') DEFAULT 'N',
                    FLENVIADOGRAPHRAG ENUM('S','N') DEFAULT 'N',
                    FLENVIADOQDRANT ENUM('S','N') DEFAULT 'N',
                    IDUSUARIOINC INT NOT NULL,
                    DTINCLUSAO TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    IDUSUARIOALT INT NOT NULL,
                    DTALTERACAO TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (IDASTCLASS) REFERENCES astclasse(IDASTCLASS) ON DELETE CASCADE
                );";
        return $sql;
    }

    public function down(): string
    {
        $sql = "DROP TABLE IF EXISTS astmethods";
        return $sql;
    }
}
