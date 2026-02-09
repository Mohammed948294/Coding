<?php

declare(strict_types=1);

namespace Dev\Controllers;

use Core\Database;

final class PlanController extends BaseController
{
    public function index(): void
    {
        $pdo = Database::pdo();
        $plans = $pdo->query('SELECT * FROM plans ORDER BY id')->fetchAll();
        $modules = $pdo->query('SELECT * FROM modules ORDER BY id')->fetchAll();
        $mapRows = $pdo->query('SELECT * FROM plan_modules')->fetchAll();
        $map = [];
        foreach ($mapRows as $r) {
            $map[$r['plan_id']][$r['module_id']] = (int) $r['enabled'];
        }
        $this->render('plans/index', compact('plans', 'modules', 'map'));
    }
}
