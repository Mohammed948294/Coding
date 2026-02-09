<?php

declare(strict_types=1);

namespace Core;

final class CSRF
{
    public static function token(string $scope = 'default'): string
    {
        if (empty($_SESSION['_csrf'][$scope])) {
            $_SESSION['_csrf'][$scope] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'][$scope];
    }

    public static function verify(string $token, string $scope = 'default'): bool
    {
        $stored = $_SESSION['_csrf'][$scope] ?? '';
        return hash_equals($stored, $token);
    }
}
