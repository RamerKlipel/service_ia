#!/bin/bash
set -e

echo "-> dowing containers and cleaning volumes..."
docker compose down > /dev/null 2>&1

echo "-> re-building images..."
docker compose build

echo "==> Subindo containers..."
docker compose up -d > /dev/null 2>&1

echo "-> Downloading Ollama models if necessary..."
docker compose exec ollama ollama pull nomic-embed-text > /dev/null 2>&1
docker compose exec ollama ollama pull qwen2.5-coder:3b > /dev/null 2>&1

echo "-> installing dependences..."
docker compose exec app php composer install > /dev/null 2>&1

echo "-> running migratations if necessary..."
docker compose exec app php artisan.php migrate > /dev/null 2>&1

echo "-> testing health check..."
curl -f http://localhost:8060/api/health > /dev/null 2>&1 && echo -e "app healthy"

docker compose exec app curl -f http://ollama:11434 > /dev/null 2>&1 && echo -e "ollama healthy"

docker compose exec app curl -f http://qdrant:6333 > /dev/null 2>&1 && echo -e "qdrant healthy"
