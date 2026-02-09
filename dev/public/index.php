<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/bootstrap.php';

use Dev\Controllers\AuthController;
use Dev\Controllers\DashboardController;
use Dev\Controllers\PlanController;
use Dev\Controllers\TenantController;
use Dev\Middlewares\DevAuthMiddleware;

$route = $_GET['r'] ?? 'dashboard';
$method = $_SERVER['REQUEST_METHOD'];

if ($route === 'login') {
    $ctrl = new AuthController();
    $method === 'POST' ? $ctrl->login() : $ctrl->loginForm();
    exit;
}
if ($route === 'logout') {
    (new AuthController())->logout();
    exit;
}

DevAuthMiddleware::handle();

switch ($route) {
    case 'dashboard':
        (new DashboardController())->index();
        break;
    case 'tenants.index':
        $ctrl = new TenantController();
        $method === 'POST' ? $ctrl->store() : $ctrl->index();
        break;
    case 'tenants.edit':
        $ctrl = new TenantController();
        $method === 'POST' ? $ctrl->update() : $ctrl->edit();
        break;
    case 'tenants.modules':
        $ctrl = new TenantController();
        $method === 'POST' ? $ctrl->saveModules() : $ctrl->modules();
        break;
    case 'plans.index':
        $ctrl = new PlanController();
        $method === 'POST' ? $ctrl->save() : $ctrl->index();
        break;
    default:
        http_response_code(404);
        echo '404';
}
