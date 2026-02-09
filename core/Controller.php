<?php

declare(strict_types=1);

namespace Core;

abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        extract($data, EXTR_OVERWRITE);
        $viewFile = $view;
        include $this->viewPath($layout);
    }

    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    abstract protected function viewPath(string $view): string;
}
