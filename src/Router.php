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
            http_response_code(404);
            header('Content-type: apllication/json');
            echo json_encode(["success" => false, "error" => "Rota não encontrada"]);
            return;
        }

        [$class, $method] = $handler;
        $controller = new $class;

        if (!empty($method) && method_exists($controller, $method)) {
            call_user_func([$controller, $method]);
        }
    }
}
