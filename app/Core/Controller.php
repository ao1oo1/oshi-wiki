<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $viewPath = __DIR__ . '/../../resources/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            Response::html('View not found: ' . htmlspecialchars($view, ENT_QUOTES, 'UTF-8'), 500);
            return;
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        Response::html($content);
    }

    protected function json(array $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    protected function redirect(string $url): void
    {
        Response::redirect($url);
    }
}