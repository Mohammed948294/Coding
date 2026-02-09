<?php

declare(strict_types=1);

namespace Core;

use PDO;

final class Auth
{
    public static function loginUser(string $username, string $password): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $_SESSION['app_user'] = [
            'id' => (int) $user['id'],
            'tenant_id' => (int) $user['tenant_id'],
            'role_id' => (int) $user['role_id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
        ];

        return true;
    }

    public static function loginDev(string $username, string $password): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM dev_users WHERE username = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $_SESSION['dev_user'] = [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
        ];

        return true;
    }

    public static function appUser(): ?array
    {
        return $_SESSION['app_user'] ?? null;
    }

    public static function devUser(): ?array
    {
        return $_SESSION['dev_user'] ?? null;
    }

    public static function logoutApp(): void
    {
        unset($_SESSION['app_user']);
    }

    public static function logoutDev(): void
    {
        unset($_SESSION['dev_user']);
    }
}
