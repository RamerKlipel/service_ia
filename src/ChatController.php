<?php

namespace App;

use App\Services\OllamaChatService;
use App\Controller;

use LLPhant\Chat\Message;
use LLPhant\Query\SemanticSearch\QuestionAnswering;

class ChatController extends Controller
{
    public array $messages;
    public string $urlOllama = "http://ollama:11434/api/";
    public ?QuestionAnswering $questionAwnsering = null;
    protected ?OllamaChatService $service = null;

    public function __construct()
    {
        $this->service = new OllamaChatService;
    }

    public function health(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['succes' => true, 'status' => 'ok']);
    }

    public function createChat(): void
    {
        try {
            $isModelEmbedingActive = $this->service->verifyModelIsActive($this->urlOllama, "nomic-embed-text");
            $isModelChatActive = $this->service->verifyModelIsActive($this->urlOllama, "qwen2.5-coder:3b");

            if (!$isModelChatActive || !$isModelEmbedingActive) {
                throw new \Exception("Modelo de llm não encontrado", 500);
            }

            $this->questionAwnsering = $this->service->createChatOllama($this->urlOllama);
        } catch (\Exception $e) {
            http_response_code($e->getCode() ?? 500);
            throw new \Exception("Ocorreu um erro!", $e->getCode() ?? 500, $e);
        }
    }

    public function askChat()
    {
        $this->createChat();
        // ! log
        $arrInput = $this->getJsonInput();
        $this->requireFields($arrInput, ['message']);
        // TODO fazer função para pegar historico de mensagens atual no banco depois
        // ! exemplo =>  $arrMessage[] = $this->model->getHistoricoMensagensChatAtual($idChat);

        $arrMessage[] = Message::user($arrInput["message"]);
        $completeAnswer = "";

        try {
            $stream = $this->questionAwnsering->answerQuestionFromChat($arrMessage);
        } catch (\Exception $e) {
            http_response_code(500);
            throw $e;
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');

        try {
            while (!$stream->eof()) {
                $chunk =  $stream->read(100);
                if ($chunk != "") {
                    $completeAnswer .= $chunk;
                    echo "data: " . json_encode($chunk) . "\n\n";
                    if (ob_get_length()) {
                        ob_flush();
                    }
                    flush();
                }
            }
        } catch (\Exception $e) {
            http_response_code(500);
            throw $e;
        }

        echo "data: [DONE]\n\n";
        // TODO fazer função para salvar no banco o novo historico depois
        // ! log
    }
}
