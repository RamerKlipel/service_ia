<?php

namespace App\Database\Migrations;

use App\Migration;

class create_conversations extends Migration
{

    public function up(): string
    {
        $sql = "CREATE TABLE conversations (
                    IDCONVERSATION INT AUTO_INCREMENT PRIMARY KEY,
                    SGUSUARIO VARCHAR(100) NOT NULL,
                    DTCREATEAT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    SGUSUARIOINC VARCHAR(100) NOT NULL,
                    DTCHANGEAT TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
        return $sql;
    }

    public function down(): string
    {
        $sql = "DROP TABLE IF EXISTS conversations";
        return $sql;
    }
}
