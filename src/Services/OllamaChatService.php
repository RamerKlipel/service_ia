<?php

namespace App\Services;

class OllamaChatService
{
    public function streamResponse(string $message, array $history): void
    {
        // TODO: montar o LLPhant OllamaChatModel aqui
        // e iterar a resposta em chunks, dando echo + flush a cada pedaço

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_implicit_flush(true);

        foreach ($this->fakeStreamChunks($message) as $chunk) {
            echo "data: " . json_encode(['chunk' => $chunk]) . "\n\n";
            flush();
        }

        echo "data: [DONE]\n\n";
        flush();
    }

    private function fakeStreamChunks(string $message): \Generator
    {
        foreach (str_split("Resposta de teste pra: $message", 5) as $piece) {
            usleep(100000);
            yield $piece;
        }
    }
}
