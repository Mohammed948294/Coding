<?php

declare(strict_types=1);

namespace App\Middlewares;

use Core\Auth;
use Core\RBAC;

final class PermissionMiddleware
{
    public static function check(string $permission): void
    {
        $user = Auth::appUser();
        if (!$user || !RBAC::can((int) $user['role_id'], $permission)) {
            http_response_code(403);
            exit('غير مسموح');
        }
    }
}
