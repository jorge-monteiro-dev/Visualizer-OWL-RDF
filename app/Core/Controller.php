<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function view(string $name, array $data = []): void
    {
        extract($data);
        $viewPath = APP_PATH . '/Views/' . $name . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '{$name}' not found at {$viewPath}");
        }
        require $viewPath;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}
