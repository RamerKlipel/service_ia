<?php

namespace App;

class Router
{
    private array $router = [];

    public function get(string $path, array $handler): void
    {
        $this->router["GET"][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->router["POST"][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        $handler = ($this->router[$method][$path] ?? null);

        if ($handler == null) {
            jsonResponse(["success" => false, "error" => "Rota não encontrada"], 404);
            return;
        }

        [$class, $method] = $handler;

        try {
            $controller = new $class;

            if (!empty($method) && method_exists($controller, $method)) {
                call_user_func([$controller, $method]);
            }
        } catch (\Exception $e) {
            jsonResponse(['success' => false, 'error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
