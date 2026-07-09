<?php

namespace App\Database\Migrations;

use App\Migration;

class create_conversations extends Migration
{

    public function up(): string
    {
        $sql = "CREATE TABLE IF NOT EXISTS conversations (
                    IDCONVERSATION INT AUTO_INCREMENT PRIMARY KEY,
                    SGUSUARIOALT VARCHAR(100) NOT NULL,
                    DTALTERACAO DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    SGUSUARIOINC VARCHAR(100) NOT NULL,
                    DTINCLUSAO DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                )";
        return $sql;
    }

    public function down(): string
    {
        $sql = "DROP TABLE IF EXISTS conversations";
        return $sql;
    }
}
