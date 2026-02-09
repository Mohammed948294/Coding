<?php

declare(strict_types=1);

namespace App\Middlewares;

use Core\Auth;

final class AppAuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::appUser()) {
            header('Location: /app/public/index.php?r=login');
            exit;
        }
    }
}
