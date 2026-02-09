<?php

declare(strict_types=1);

namespace Core;

final class FeatureGate
{
    public static function isModuleEnabled(int $tenantId, string $moduleKey): bool
    {
        $pdo = Database::pdo();

        $stmt = $pdo->prepare('SELECT tm.enabled FROM tenant_modules tm JOIN modules m ON m.id = tm.module_id WHERE tm.tenant_id = ? AND m.key_name = ? LIMIT 1');
        $stmt->execute([$tenantId, $moduleKey]);
        $override = $stmt->fetchColumn();

        if ($override !== false && $override !== null) {
            return (int) $override === 1;
        }

        $stmt = $pdo->prepare('SELECT pm.enabled FROM tenants t JOIN plan_modules pm ON pm.plan_id = t.plan_id JOIN modules m ON m.id = pm.module_id WHERE t.id = ? AND m.key_name = ? LIMIT 1');
        $stmt->execute([$tenantId, $moduleKey]);
        $planEnabled = $stmt->fetchColumn();
        return (int) $planEnabled === 1;
    }
}
