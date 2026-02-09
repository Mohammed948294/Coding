<?php

declare(strict_types=1);

namespace Dev\Controllers;

use Core\Audit;
use Core\Auth;
use Core\CSRF;
use Dev\Models\TenantModel;

final class TenantController extends BaseController
{
    public function index(): void
    {
        $model = new TenantModel();
        $tenants = $model->all();
        $plans = $model->plans();
        $this->render('tenants/index', ['tenants' => $tenants, 'plans' => $plans, 'csrf' => CSRF::token('tenant_create')]);
    }

    public function store(): void
    {
        if (!CSRF::verify($_POST['_csrf'] ?? '', 'tenant_create')) {
            exit('Invalid CSRF');
        }

        $model = new TenantModel();
        $tenantId = $model->create([
            'name' => $_POST['name'],
            'slug' => $_POST['slug'],
            'plan_id' => (int) $_POST['plan_id'],
            'organization_name' => $_POST['name'],
            'max_users' => (int)($_POST['max_users'] ?? 25),
            'max_storage_mb' => (int)($_POST['max_storage_mb'] ?? 1024),
            'max_documents' => (int)($_POST['max_documents'] ?? 50000),
            'theme_mode' => $_POST['theme_mode'] ?? 'light',
            'primary_color' => $_POST['primary_color'] ?? '#0d6efd',
            'sidebar_modules' => $_POST['sidebar_modules'] ?? ['dashboard'],
            'dashboard_widgets' => $_POST['dashboard_widgets'] ?? ['total'],
        ]);

        $model->createAdmin([
            'tenant_id' => $tenantId,
            'role_id' => $model->roleSuperAdmin($tenantId),
            'username' => $_POST['admin_username'],
            'password' => $_POST['admin_password'],
            'full_name' => $_POST['admin_full_name'],
        ]);

        Audit::log('dev', (int)Auth::devUser()['id'], null, 'create', 'tenant', $tenantId);
        $this->redirect('/dev/public/index.php?r=tenants.index');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $model = new TenantModel();
        $tenant = $model->find($id);
        if (!$tenant) {
            exit('Tenant not found');
        }
        $plans = $model->plans();
        $this->render('tenants/edit', ['tenant' => $tenant, 'plans' => $plans, 'csrf' => CSRF::token('tenant_edit')]);
    }

    public function update(): void
    {
        if (!CSRF::verify($_POST['_csrf'] ?? '', 'tenant_edit')) {
            exit('Invalid CSRF');
        }

        $id = (int)($_POST['id'] ?? 0);
        (new TenantModel())->updateTenant($id, [
            'name' => $_POST['name'],
            'slug' => $_POST['slug'],
            'plan_id' => (int)$_POST['plan_id'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'organization_name' => $_POST['organization_name'] ?? $_POST['name'],
            'numbering_pattern' => $_POST['numbering_pattern'] ?? 'DOC-{Y}-{SEQ}',
            'default_confidentiality' => $_POST['default_confidentiality'] ?? 'normal',
            'max_users' => (int)($_POST['max_users'] ?? 25),
            'max_storage_mb' => (int)($_POST['max_storage_mb'] ?? 1024),
            'max_documents' => (int)($_POST['max_documents'] ?? 50000),
            'theme_mode' => $_POST['theme_mode'] ?? 'light',
            'primary_color' => $_POST['primary_color'] ?? '#0d6efd',
            'sidebar_modules' => $_POST['sidebar_modules'] ?? ['dashboard'],
            'dashboard_widgets' => $_POST['dashboard_widgets'] ?? ['total'],
        ]);

        Audit::log('dev', (int)Auth::devUser()['id'], null, 'update', 'tenant', $id);
        $this->redirect('/dev/public/index.php?r=tenants.index');
    }

    public function modules(): void
    {
        $tenantId = (int) ($_GET['tenant_id'] ?? 0);
        $model = new TenantModel();
        $modules = $model->modules();
        $map = $model->tenantModuleMap($tenantId);
        $this->render('tenants/modules', ['tenantId' => $tenantId, 'modules' => $modules, 'map' => $map, 'csrf' => CSRF::token('tenant_modules')]);
    }

    public function saveModules(): void
    {
        if (!CSRF::verify($_POST['_csrf'] ?? '', 'tenant_modules')) {
            exit('Invalid CSRF');
        }

        $tenantId = (int) $_POST['tenant_id'];
        $model = new TenantModel();
        foreach ($model->modules() as $module) {
            $enabled = isset($_POST['module'][$module['id']]) ? 1 : 0;
            $model->setOverride($tenantId, (int)$module['id'], $enabled);
        }

        Audit::log('dev', (int)Auth::devUser()['id'], null, 'update', 'tenant_modules', $tenantId);
        $this->redirect('/dev/public/index.php?r=tenants.index');
    }
}
