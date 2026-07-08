<?php
require_once __DIR__.'/vendor/autoload.php';

use LLPhant\Embeddings\DataReader\FileDataReader;
use LLPhant\Embeddings\DocumentSplitter\DocumentSplitter;
use LLPhant\Embeddings\EmbeddingGenerator\Ollama\OllamaEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use LLPhant\OllamaConfig;

$embeddingConfig = new OllamaConfig();
$embeddingConfig->model = 'nomic-embed-text';
$embeddingConfig->url = 'http://ollama:11434/api/';

$fileReader = new FileDataReader(
    'src/utils/functions.php', extensions: ['php']
);
$documents = $fileReader->getDocuments();

$splitter = new DocumentSplitter();
$chunks = $splitter->splitDocuments($documents, 500);

$embeddingGenerator = new OllamaEmbeddingGenerator($embeddingConfig);
$chunksEmbeddings =  $embeddingGenerator->embedDocuments($chunks);

$vectorStore = new FileSystemVectorStore("vault-embeddings.json");
$vectorStore->addDocuments($chunksEmbeddings);

// TODO alterar esse arquivo para realizar o gerador de relação dos arquivos .php em json salvando no banco, depois construir uma tela para verificar esse json e aprovar, depois disso, fazer outro arquivo que vai rodar atuando sobre os jsons do banco que estao aprovados, ja tem ate a tabela -> astclasses
