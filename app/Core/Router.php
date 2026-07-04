<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        if (is_callable($handler)) {
            call_user_func($handler);
            return;
        }

        [$class, $action] = $handler;

        if (!class_exists($class)) {
            http_response_code(500);
            echo 'Controller not found: ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
            return;
        }

        $controller = new $class();

        if (!method_exists($controller, $action)) {
            http_response_code(500);
            echo 'Action not found: ' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
            return;
        }

        $controller->$action();
    }
}