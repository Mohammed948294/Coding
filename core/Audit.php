<?php

declare(strict_types=1);

namespace Core;

final class Audit
{
    public static function log(string $actorType, ?int $actorId, ?int $tenantId, string $action, string $entity, ?int $entityId = null): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('INSERT INTO audit_logs (tenant_id, actor_type, actor_id, action, entity, entity_id, ip, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $tenantId,
            $actorType,
            $actorId,
            $action,
            $entity,
            $entityId,
            $_SERVER['REMOTE_ADDR'] ?? 'cli',
            Helpers::now(),
        ]);
    }
}
