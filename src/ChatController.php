<?php

namespace App;

use App\Services\OllamaChatService;

class ChatController
{
    public function chat(): void
    {
        $arrInput = json_decode(file_get_contents('php://input'), true);

        $message = $arrInput['message'] ?? null;
        $history = $arrInput['history'] ?? [];

        if (!$message) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['succes' => false, 'error' => 'Campo "message" é obrigatório']);
            return;
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no'); // pro nginx não bufferizar

        $service = new OllamaChatService;
        $service->streamResponse($message, $history);
    }

    public function health(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['succes' => true, 'status' => 'ok']);
    }
}
