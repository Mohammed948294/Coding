<?php

declare(strict_types=1);

namespace Dev\Controllers;

use Core\CSRF;
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
        $this->render('plans/index', ['plans' => $plans, 'modules' => $modules, 'map' => $map, 'csrf' => CSRF::token('plan_modules')]);
    }

    public function save(): void
    {
        if (!CSRF::verify($_POST['_csrf'] ?? '', 'plan_modules')) {
            exit('Invalid CSRF');
        }

        $pdo = Database::pdo();
        $plans = $pdo->query('SELECT id FROM plans')->fetchAll();
        $modules = $pdo->query('SELECT id FROM modules')->fetchAll();

        $stmt = $pdo->prepare('INSERT INTO plan_modules (plan_id,module_id,enabled) VALUES (?,?,?) ON DUPLICATE KEY UPDATE enabled=VALUES(enabled)');
        foreach ($plans as $plan) {
            foreach ($modules as $module) {
                $enabled = isset($_POST['pm'][$plan['id']][$module['id']]) ? 1 : 0;
                $stmt->execute([(int)$plan['id'], (int)$module['id'], $enabled]);
            }
        }

        header('Location: /dev/public/index.php?r=plans.index');
        exit;
    }
}
