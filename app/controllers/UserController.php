<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use Core\Auth;
use Core\CSRF;

final class UserController extends BaseController
{
    public function index(): void
    {
        $tenantId = (int) Auth::appUser()['tenant_id'];
        $model = new UserModel();
        $users = $model->listUsers($tenantId);
        $roles = $model->roles($tenantId);
        $this->render('users/index', ['users' => $users, 'roles' => $roles, 'csrf' => CSRF::token('user_create')]);
    }

    public function store(): void
    {
        if (!CSRF::verify($_POST['_csrf'] ?? '', 'user_create')) {
            exit('Invalid CSRF');
        }
        $tenantId = (int) Auth::appUser()['tenant_id'];
        (new UserModel())->create([
            'tenant_id' => $tenantId,
            'role_id' => (int) $_POST['role_id'],
            'username' => $_POST['username'],
            'password' => $_POST['password'],
            'full_name' => $_POST['full_name'],
        ]);
        $this->redirect('/app/public/index.php?r=users.index');
    }
}
