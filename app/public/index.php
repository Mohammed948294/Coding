<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DocumentController;
use App\Controllers\SearchController;
use App\Controllers\UserController;
use App\Middlewares\AppAuthMiddleware;
use App\Middlewares\ModuleMiddleware;
use App\Middlewares\PermissionMiddleware;

$route = $_GET['r'] ?? 'dashboard';
$method = $_SERVER['REQUEST_METHOD'];

if ($route === 'login') {
    $controller = new AuthController();
    $method === 'POST' ? $controller->login() : $controller->loginForm();
    exit;
}
if ($route === 'logout') {
    (new AuthController())->logout();
    exit;
}

AppAuthMiddleware::handle();

switch ($route) {
    case 'dashboard':
        (new DashboardController())->index();
        break;
    case 'inbound.index':
        ModuleMiddleware::check('inbound');
        PermissionMiddleware::check('inbound.view');
        (new DocumentController())->index('inbound');
        break;
    case 'inbound.create':
        ModuleMiddleware::check('inbound');
        PermissionMiddleware::check('inbound.create');
        $ctrl = new DocumentController();
        $method === 'POST' ? $ctrl->store('inbound') : $ctrl->createForm('inbound');
        break;
    case 'outbound.index':
        ModuleMiddleware::check('outbound');
        PermissionMiddleware::check('outbound.view');
        (new DocumentController())->index('outbound');
        break;
    case 'outbound.create':
        ModuleMiddleware::check('outbound');
        PermissionMiddleware::check('outbound.create');
        $ctrl = new DocumentController();
        $method === 'POST' ? $ctrl->store('outbound') : $ctrl->createForm('outbound');
        break;
    case 'documents.show':
        PermissionMiddleware::check('inbound.view');
        (new DocumentController())->show((int)($_GET['id'] ?? 0));
        break;
    case 'documents.download':
        PermissionMiddleware::check('documents.download');
        (new DocumentController())->download((int)($_GET['id'] ?? 0));
        break;
    case 'search':
        ModuleMiddleware::check('archive');
        PermissionMiddleware::check('inbound.view');
        (new SearchController())->index();
        break;
    case 'users.index':
        ModuleMiddleware::check('user_management');
        PermissionMiddleware::check('users.manage');
        $ctrl = new UserController();
        $method === 'POST' ? $ctrl->store() : $ctrl->index();
        break;
    default:
        http_response_code(404);
        echo '404';
}
