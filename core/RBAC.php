<?php

declare(strict_types=1);

namespace Core;

final class RBAC
{
    public static function can(int $roleId, string $permission): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ? AND p.key_name = ?');
        $stmt->execute([$roleId, $permission]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
