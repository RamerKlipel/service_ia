# micro-service-ia

## Visão Geral

Este repositório contém um micro-serviço escrito em PHP cuja função primária é expor um endpoint de chat baseado em LLM com streaming (SSE). O serviço fornece um endpoint HTTP para conversas (streaming de respostas), além de um healthcheck e suporte a migrações de banco.

## Objetivo Primário

Fornecer uma API simples que permita a um frontend receber respostas incrementais (SSE) de um modelo de linguagem (integração prevista com Ollama/llphant), com armazenamento e migrações suportados via banco de dados.

## Tecnologias principais

- **Linguagem:** PHP 8.2+
- **Gerenciador de dependências:** Composer
- **Containerização:** Docker + docker-compose
- ** Serviços auxiliares:** MySQL, Ollama (cliente LLM), Qdrant (vetor DB)
- **Principais libs:** theodo-group/llphant, symfony/http-client, nyholm/psr7, nikic/php-parser

## Entrypoints & Arquivos chave

- `public/index.php` — rota HTTP principal (roteamento das APIs)
- `src/ChatController.php` — implementação dos endpoints `/api/chat` e `/api/health`
- `src/Services/OllamaChatService.php` — camada responsável pelo streaming de respostas (implementação atual de exemplo/test)
- `artisan.php` — utilitário CLI para executar migrations (`migrate`)
- `Dockerfile` e `docker-compose.yaml` — definição de containers e serviços (nginx, app, mysql, ollama, qdrant)

## Endpoints

- **POST /api/chat** — endpoint principal de chat; retorna respostas via Server-Sent Events (SSE). Consulte `src/ChatController.php`.
- **GET /api/health** — health check do serviço.

## Rodando o serviço

Usando Docker (recomendado):

```bash
docker-compose up --build
```

Executando localmente (desenvolvimento leve):

```bash
composer install
```

Executar migrações:

```bash
php artisan.php migrate
```

## Baixar modelos (Ollama)

Se estiver usando o serviço Ollama para hospedar os modelos, você pode baixar os modelos localmente com o CLI do `ollama` ou executando o comando dentro do container `ollama` (quando usar `docker compose`):

```bash
docker compose exec ollama ollama pull nomic-embed-text
docker compose exec ollama ollama pull qwen2.5-code:3b
```

Observação: os modelos podem ser grandes; verifique espaço em disco e memória antes do download.

## Configurações / Variáveis de ambiente

As variáveis principais são definidas em `docker-compose.yaml` e lidas por `config/app.php`:

- `DB_DRIVER`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`
- `OLLAMA_HOST`
- `QDRANT_HOST`

## Estado atual e Observações

- O endpoint de chat já está implementado para SSE, porém `OllamaChatService` contém uma implementação de streaming de exemplo/test — ainda precisa ser conectado ao cliente Ollama/llphant para streaming real.
- Migrações e estrutura básica do DB estão presentes em `src/Database/Migrations/`.
- Há um arquivo `composer.json` com as dependências necessárias.

## Próximos passos sugeridos

1. Adicionar `.env.example` com todas as variáveis necessárias.
2. Implementar/integrar o cliente real do Ollama/llphant em `src/Services/OllamaChatService.php` para streaming verdadeiro.
3. Adicionar testes automatizados para endpoints chave e integração de streaming.
4. Documentar contratos de API com exemplos SSE para consumidores frontend.

## Contato

Para dúvidas ou suporte, responda neste repositório ou abra uma issue.
