<?php

declare(strict_types=1);

namespace Dev\Middlewares;

use Core\Auth;

final class DevAuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::devUser()) {
            header('Location: /dev/public/index.php?r=login');
            exit;
        }
    }
}
