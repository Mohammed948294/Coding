<?php

declare(strict_types=1);

namespace App\Middlewares;

use Core\FeatureGate;
use Core\TenantContext;

final class ModuleMiddleware
{
    public static function check(string $moduleKey): void
    {
        if (!FeatureGate::isModuleEnabled(TenantContext::id(), $moduleKey)) {
            http_response_code(403);
            exit('هذه الميزة غير مفعلة للباقتك');
        }
    }
}
