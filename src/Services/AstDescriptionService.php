<?php

namespace App\Services;

use LLPhant\OllamaConfig;
use LLPhant\Chat\OllamaChat;

class AstDescriptionService
{
    private OllamaChat $chat;
    private string $modelName;

    public function __construct(string $urlOllama, string $modelName = 'qwen2.5-coder:3b')
    {
        $this->modelName = $modelName;

        $config = new OllamaConfig();
        $config->url = $urlOllama;
        $config->model = $modelName;
        $config->modelOptions = ["timeout" => 120];

        $this->chat = new OllamaChat($config);
        $this->chat->setSystemMessage(
            "Você é um especialista em documentação de código PHP legado.
            Gere uma descrição curta e objetiva (2-4 frases) explicando o propósito
            e comportamento do código fornecido. Seja direto, sem floreios.
            Responda apenas com a descrição, sem introduções."
        );
    }

    public function getModelName(): string
    {
        return $this->modelName;
    }

    public function generateClassDescription(array $classe): string
    {
        $prompt = "Sistema: {$classe['sistema']}\n";
        $prompt .= "Classe: {$classe['namespace']}\\{$classe['name']} (tipo: {$classe['type']})\n";
        if ($classe['parent']) {
            $prompt .= "Extends: {$classe['parent']}\n";
        }
        $prompt .= "Código:\n{$classe['code']}\n\nDescreva o propósito desta classe.";

        return $this->chat->generateText($prompt);
    }

    public function generateMethodDescription(string $className, array $method): string
    {
        $prompt = "Classe: $className\nMétodo: {$method['name']}\n";
        $prompt .= "Código:\n{$method['code']}\n\nDescreva o que esse método faz.";

        return $this->chat->generateText($prompt);
    }

    public function generateFunctionDescription(array $func): string
    {
        $prompt = "Função solta (sem classe): {$func['name']}\n";
        $prompt .= "Código:\n{$func['code']}\n\nDescreva o que essa função faz.";

        return $this->chat->generateText($prompt);
    }
}
