<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../src/utils/functions.php';

$router = new App\Router;
$router->post('/api/chat', [App\ChatController::class, 'askChat']);
$router->get('/api/health', [App\ChatController::class, 'health']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
