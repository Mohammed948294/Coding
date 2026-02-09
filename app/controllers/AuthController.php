<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Auth;
use Core\Audit;
use Core\CSRF;

final class AuthController extends BaseController
{
    public function loginForm(): void
    {
        $this->render('auth/login', ['csrf' => CSRF::token('app_login')], 'layouts/guest');
    }

    public function login(): void
    {
        if (!CSRF::verify($_POST['_csrf'] ?? '', 'app_login')) {
            exit('Invalid CSRF');
        }

        if (Auth::loginUser($_POST['username'] ?? '', $_POST['password'] ?? '')) {
            $u = Auth::appUser();
            Audit::log('user', $u['id'], $u['tenant_id'], 'login', 'user', $u['id']);
            $this->redirect('/app/public/index.php?r=dashboard');
        }

        $this->render('auth/login', ['error' => 'بيانات الدخول غير صحيحة', 'csrf' => CSRF::token('app_login')], 'layouts/guest');
    }

    public function logout(): void
    {
        $u = Auth::appUser();
        if ($u) {
            Audit::log('user', $u['id'], $u['tenant_id'], 'logout', 'user', $u['id']);
        }
        Auth::logoutApp();
        $this->redirect('/app/public/index.php?r=login');
    }
}
