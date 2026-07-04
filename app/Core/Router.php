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
        $request = new Request();
        $path = $request->path();

        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            Response::notFound();
            return;
        }

        if (is_callable($handler)) {
            call_user_func($handler, $request);
            return;
        }

        [$class, $action] = $handler;

        if (!class_exists($class)) {
            Response::html('Controller not found: ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8'), 500);
            return;
        }

        $controller = new $class();

        if (!method_exists($controller, $action)) {
            Response::html('Action not found: ' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8'), 500);
            return;
        }

        $controller->$action($request);
    }
}