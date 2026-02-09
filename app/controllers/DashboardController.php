<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DocumentModel;
use Core\Auth;
use Core\Database;
use Core\FeatureGate;

final class DashboardController extends BaseController
{
    public function index(): void
    {
        $tenantId = (int) Auth::appUser()['tenant_id'];
        $stats = (new DocumentModel())->dashboardStats($tenantId);

        $stmt = Database::pdo()->prepare('SELECT * FROM audit_logs WHERE tenant_id=? ORDER BY id DESC LIMIT 10');
        $stmt->execute([$tenantId]);
        $activities = $stmt->fetchAll();

        $settingsStmt = Database::pdo()->prepare('SELECT * FROM tenant_settings WHERE tenant_id=? LIMIT 1');
        $settingsStmt->execute([$tenantId]);
        $tenantSettings = $settingsStmt->fetch() ?: [];
        $widgets = json_decode((string)($tenantSettings['dashboard_widgets_json'] ?? ''), true);
        if (!is_array($widgets)) {
            $widgets = ['total', 'inbound', 'outbound', 'today', 'activities'];
        }

        $features = [
            'inbound' => FeatureGate::isModuleEnabled($tenantId, 'inbound_documents'),
            'outbound' => FeatureGate::isModuleEnabled($tenantId, 'outbound_documents'),
            'tasks' => FeatureGate::isModuleEnabled($tenantId, 'tasks'),
            'reports' => FeatureGate::isModuleEnabled($tenantId, 'reports'),
        ];

        $this->render('dashboard/index', compact('stats', 'activities', 'widgets', 'features'));
    }
}
