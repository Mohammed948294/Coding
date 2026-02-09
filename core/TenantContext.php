<?php

declare(strict_types=1);

namespace Core;

final class TenantContext
{
    public static function id(): int
    {
        return (int) (Auth::appUser()['tenant_id'] ?? 0);
    }

    public static function enforce(int $tenantId): void
    {
        if (self::id() !== $tenantId) {
            http_response_code(403);
            exit('Forbidden tenant scope');
        }
    }
}
