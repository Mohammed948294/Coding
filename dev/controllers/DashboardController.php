<?php

declare(strict_types=1);

namespace Dev\Controllers;

use Dev\Models\TenantModel;
use Core\Database;

final class DashboardController extends BaseController
{
    public function index(): void
    {
        $model = new TenantModel();
        $summary = $model->healthSummary();
        $storage = $model->storageByTenant();
        $logs = Database::pdo()->query('SELECT * FROM audit_logs ORDER BY id DESC LIMIT 20')->fetchAll();
        $this->render('dashboard/index', compact('summary', 'storage', 'logs'));
    }
}
