<?php

declare(strict_types=1);

namespace Dev\Controllers;

use Core\Auth;
use Core\Audit;
use Core\CSRF;

final class AuthController extends BaseController
{
    public function loginForm(): void
    {
        $this->render('auth/login', ['csrf' => CSRF::token('dev_login')], 'layouts/guest');
    }

    public function login(): void
    {
        if (!CSRF::verify($_POST['_csrf'] ?? '', 'dev_login')) {
            exit('Invalid CSRF');
        }
        if (Auth::loginDev($_POST['username'] ?? '', $_POST['password'] ?? '')) {
            $u = Auth::devUser();
            Audit::log('dev', (int)$u['id'], null, 'login', 'dev_user', (int)$u['id']);
            $this->redirect('/dev/public/index.php?r=dashboard');
        }
        $this->render('auth/login', ['error' => 'بيانات غير صحيحة', 'csrf' => CSRF::token('dev_login')], 'layouts/guest');
    }

    public function logout(): void
    {
        Auth::logoutDev();
        $this->redirect('/dev/public/index.php?r=login');
    }
}
