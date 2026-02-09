<?php

declare(strict_types=1);

namespace Core;

final class Helpers
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function randomFileName(string $extension): string
    {
        return bin2hex(random_bytes(16)) . '.' . $extension;
    }

    public static function flash(string $key, ?string $value = null): ?string
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }

        $message = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $message;
    }
}
