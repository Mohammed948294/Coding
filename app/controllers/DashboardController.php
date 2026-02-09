<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DocumentModel;
use Core\Auth;
use Core\Database;

final class DashboardController extends BaseController
{
    public function index(): void
    {
        $tenantId = (int) Auth::appUser()['tenant_id'];
        $stats = (new DocumentModel())->dashboardStats($tenantId);
        $stmt = Database::pdo()->prepare('SELECT * FROM audit_logs WHERE tenant_id=? ORDER BY id DESC LIMIT 10');
        $stmt->execute([$tenantId]);
        $activities = $stmt->fetchAll();

        $this->render('dashboard/index', compact('stats', 'activities'));
    }
}
