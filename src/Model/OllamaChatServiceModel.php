<?php

namespace App\Model;

use App\Database;
use App\Model;

class OllamaChatServiceModel extends Model
{
    public function getHistoryMessages(string $sgUsuario, ?int $idChat = null): array
    {
        if ($idChat == null) {
            return [];
        }

        $arrPdo = [
            ":SGUSUARIO" => $sgUsuario,
            ":IDCHAT" => $idChat,
        ];

        $sql = "SELECT pa.IDMESSAGE, pa.IDCONVERSATION, pa.TPROLE, pa.DSCONTENT, pa.SGUSUARIOINC, pa.DTINCLUSAO
                FROM messages pa
                JOIN conversations c ON c.IDCONVERSATION = pa.IDCONVERSATION
                WHERE pa.SGUSUARIOINC = :SGUSUARIO AND c.IDCONVERSATION = :IDCHAT";
        $arrResult = Database::ExecuteSqlData($sql, $arrPdo);

        return $arrResult ?? [];
    }

    public function saveNewMessages(array $arrMessages, string $sgUsuario, ?int $idChat = null): ?int
    {

        $messageUser = $arrMessages["USER"];
        $messageAssistant = $arrMessages["ASSISTANT"];

        $arrPdo = [
            ":DSCONTENTUSER" => $messageUser,
            ":DSCONTENTASSISTANT" => $messageAssistant,
            ":SGUSUARIOINC" => $sgUsuario,
        ];

        $sqlInsertUser = "($idChat, 'U', :DSCONTENTUSER, :SGUSUARIOINC)";
        $sqlInsertAssistant = ",($idChat, 'A', :DSCONTENTASSISTANT, :SGUSUARIOINC)";

        $sql = "INSERT INTO messages (IDCONVERSATION, TPROLE, DSCONTENT, SGUSUARIOINC)
                    values $sqlInsertUser $sqlInsertAssistant";

        return Database::ExecuteSql($sql, $arrPdo);
    }

    public function criaChat(string $sgUsuario): int
    {
        $idChat = (int)Database::insert("conversations", ["SGUSUARIOALT" => ":SGUSUARIOALT", "SGUSUARIOINC" => ":SGUSUARIOINC"], [":SGUSUARIOALT" => $sgUsuario, ":SGUSUARIOINC" => $sgUsuario]);
        return $idChat;
    }
}
