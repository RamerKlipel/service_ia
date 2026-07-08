<?php

namespace App\Services;

use LLPhant\Embeddings\EmbeddingGenerator\Ollama\OllamaEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use LLPhant\OllamaConfig;
use LLPhant\Chat\OllamaChat;
use LLPhant\Query\SemanticSearch\QuestionAnswering;
use GuzzleHttp\Client;

class OllamaChatService
{
    public ?Client $client = null;

    public function verifyModelIsActive(string $baseUrl, string $model): bool
    {
        $arrModelsAvailable = [];
        $baseUrl = trim($baseUrl, "/")."/tags/";

        $this->createClientGuzzle();

        $objResponse = $this->client->get($baseUrl);

        $httpsResponseCode = $objResponse->getStatusCode();
        $jsonModels = $objResponse->getBody()->getContents();

        if ($jsonModels === false || $httpsResponseCode != 200) {
            return false;
        }

        $arrModels = json_decode($jsonModels, true);

        if (!isset($arrModels["models"]) || !is_array($arrModels["models"])) {
            return false;
        }

        foreach ($arrModels["models"] as $arrModel) {
            $arrModelsAvailable[] = ($arrModel["name"] ?? null);
        }

        $arrModelsAvailable = array_filter($arrModelsAvailable);

        if (empty($arrModelsAvailable)) {
            return false;
        }

        $isModelAvailable = false;
        foreach ($arrModelsAvailable as $modelAvailable) {
            if (str_starts_with($modelAvailable, $model)) {
                $isModelAvailable = true;
            }
        }

        return $isModelAvailable;
    }

    public function createChatOllama(string $urlOllama): QuestionAnswering
    {
        $embeddingConfig = new OllamaConfig();
        $embeddingConfig->url = $urlOllama;
        $embeddingConfig->model = 'nomic-embed-text';

        $chatConfig = new OllamaConfig();
        $chatConfig->url = $urlOllama;
        $chatConfig->model = 'qwen2.5-coder:3b';
        // $chatConfig->model = 'qwen2.5-coder:7b';
        $chatConfig->formatJson = true;
        $chatConfig->modelOptions = ["timeout" => 120];

        $vectorStore = new FileSystemVectorStore('vault-embeddings.json');

        $embeddingGenerator = new OllamaEmbeddingGenerator($embeddingConfig);

        $chat = new OllamaChat($chatConfig);

        $chat->setSystemMessage(
            "Você é um assistente especializado no framework PHP interno da empresa.
            Responda apenas com base na documentação fornecida.
            Se a resposta não estiver na documentação, diga explicitamente que não sabe.
            Responda sempre em português."
        );

        return new QuestionAnswering($vectorStore, $embeddingGenerator, $chat);
    }

    public function createClientGuzzle(array $arrParams = []): Client
    {
        if (empty($this->client)) {
            $this->client = new Client($arrParams);
        }

        return $this->client;
    }
}
