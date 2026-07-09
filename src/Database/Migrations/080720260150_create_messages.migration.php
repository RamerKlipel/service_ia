<?php

namespace App\Database\Migrations;

use App\Migration;

class create_messages extends Migration
{

    public function up(): string
    {
        $sql = "CREATE TABLE messages (
                    IDMESSAGES INT AUTO_INCREMENT PRIMARY KEY,
                    IDCONVERSATION INT NOT NULL,
                    role ENUM('user', 'assistant') NOT NULL,
                    DSCONTENT TEXT NOT NULL,
                    DTCREATEAT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    SGUSUARIOINC VARCHAR(100) NOT NULL,
                    DTCHANGEAT TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    CONSTRAINT FK_MESSAGES_CONVERSATION FOREIGN KEY (IDCONVERSATION) REFERENCES conversations (IDCONVERSATION) ON UPDATE CASCADE ON DELETE CASCADE
                )";
        return $sql;
    }

    public function down(): string
    {
        $sql = "DROP TABLE IF EXISTS messages";
        return $sql;
    }
}
