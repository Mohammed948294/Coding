<?php

declare(strict_types=1);

$config = require __DIR__ . '/../core/config.php';
$db = $config['db'];
$dbName = (string) $db['database'];

try {
    $rootDsn = sprintf('mysql:host=%s;port=%d;charset=%s', $db['host'], $db['port'], $db['charset'] ?? 'utf8mb4');
    $pdo = new PDO($rootDsn, $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "[ERROR] Database connection failed. Update core/config.php and ensure MySQL is running.\n");
    fwrite(STDERR, "[ERROR] " . $e->getMessage() . "\n");
    exit(1);
}


$pdo->exec(sprintf('CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', $dbName));
$pdo->exec(sprintf('USE `%s`', $dbName));

$requiredTables = ['users', 'dev_users', 'roles', 'permissions', 'role_permissions', 'tenants'];
$placeholders = implode(',', array_fill(0, count($requiredTables), '?'));
$check = $pdo->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = ? AND table_name IN ($placeholders)");
$check->execute(array_merge([$dbName], $requiredTables));
$existing = $check->fetchAll(PDO::FETCH_COLUMN);

if (count($existing) < count($requiredTables)) {
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    if ($schema === false) {
        throw new RuntimeException('Unable to read database/schema.sql');
    }

    $schema = preg_replace('/CREATE DATABASE IF NOT EXISTS\s+`?\w+`?/i', 'CREATE DATABASE IF NOT EXISTS `' . $dbName . '`', $schema);
    $schema = preg_replace('/USE\s+`?\w+`?;/i', 'USE `' . $dbName . '`;', $schema);

    $statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $schema) ?: []));
    foreach ($statements as $sql) {
        $pdo->exec($sql);
    }

    echo "[OK] Schema initialized for database: {$dbName}\n";
} else {
    echo "[OK] Required tables already exist, skipping schema import.\n";
}

$now = date('Y-m-d H:i:s');

$permissions = [
    'inbound.view','inbound.create','inbound.edit','inbound.delete',
    'outbound.view','outbound.create','outbound.edit','outbound.delete',
    'documents.download','users.manage','roles.manage','settings.manage','reports.view','reports.export'
];
$permStmt = $pdo->prepare('INSERT IGNORE INTO permissions (key_name,description) VALUES (?,?)');
foreach ($permissions as $perm) {
    $permStmt->execute([$perm, $perm]);
}

$modules = [
    'inbound_documents','outbound_documents','attachments','search','tags','tasks','audit_logs','reports',
    'multi_users','export_pdf','export_excel','settings','workflow','integrations','backup_restore'
];
$moduleStmt = $pdo->prepare('INSERT IGNORE INTO modules (name,key_name,is_active) VALUES (?,?,1)');
foreach ($modules as $key) {
    $moduleStmt->execute([ucwords(str_replace('_', ' ', $key)), $key]);
}

foreach (['Small', 'Medium', 'Enterprise'] as $plan) {
    $pdo->prepare('INSERT IGNORE INTO plans (name,description) VALUES (?,?)')
        ->execute([$plan, $plan . ' Plan']);
}

$smallPlanId = (int) $pdo->query("SELECT id FROM plans WHERE name='Small' LIMIT 1")->fetchColumn();
$mediumPlanId = (int) $pdo->query("SELECT id FROM plans WHERE name='Medium' LIMIT 1")->fetchColumn();
$enterprisePlanId = (int) $pdo->query("SELECT id FROM plans WHERE name='Enterprise' LIMIT 1")->fetchColumn();

$devPassword = password_hash('superadmin123', PASSWORD_BCRYPT);
$pdo->prepare('INSERT INTO dev_users (username,password_hash,full_name,is_superadmin,is_active,created_at) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), is_superadmin=VALUES(is_superadmin), is_active=VALUES(is_active)')
    ->execute(['superadmin', $devPassword, 'System Superadmin', 1, 1, $now]);

$pdo->prepare('INSERT INTO tenants (id,name,slug,plan_id,is_active,created_at) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name), slug=VALUES(slug), plan_id=VALUES(plan_id), is_active=VALUES(is_active)')
    ->execute([1, 'Demo Org', 'demo-org', $mediumPlanId, 1, $now]);

$pdo->prepare('INSERT INTO tenant_settings (tenant_id,organization_name,numbering_pattern,default_confidentiality,max_users,max_storage_mb,max_documents,theme_mode,primary_color,sidebar_modules_json,dashboard_widgets_json,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE organization_name=VALUES(organization_name), updated_at=VALUES(updated_at)')
    ->execute([
        1,
        'Demo Org',
        'DOC-{Y}-{SEQ}',
        'normal',
        50,
        2048,
        100000,
        'light',
        '#0d6efd',
        json_encode(['dashboard','inbound_documents','outbound_documents','search','multi_users'], JSON_UNESCAPED_UNICODE),
        json_encode(['total','inbound','outbound','today','activities'], JSON_UNESCAPED_UNICODE),
        $now,
    ]);

$roleStmt = $pdo->prepare('INSERT IGNORE INTO roles (tenant_id,name,key_name,created_at) VALUES (?,?,?,?)');
$roleStmt->execute([1, 'Tenant Super Admin', 'tenant_super_admin', $now]);
$roleStmt->execute([1, 'Staff', 'staff', $now]);
$superRoleId = (int) $pdo->query("SELECT id FROM roles WHERE tenant_id=1 AND key_name='tenant_super_admin' LIMIT 1")->fetchColumn();

$rpStmt = $pdo->prepare('INSERT IGNORE INTO role_permissions (role_id,permission_id) VALUES (?,?)');
foreach ($pdo->query('SELECT id FROM permissions')->fetchAll(PDO::FETCH_COLUMN) as $pid) {
    $rpStmt->execute([$superRoleId, (int) $pid]);
}

$tenantPassword = password_hash('admin123', PASSWORD_BCRYPT);
$pdo->prepare('INSERT INTO users (tenant_id,role_id,username,password_hash,full_name,is_active,created_at) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), role_id=VALUES(role_id), is_active=VALUES(is_active)')
    ->execute([1, $superRoleId, 'admin', $tenantPassword, 'Tenant Administrator', 1, $now]);

$pdo->prepare('INSERT IGNORE INTO departments (tenant_id,name) VALUES (1,?),(1,?),(1,?)')
    ->execute(['الإدارة العامة', 'الموارد البشرية', 'المالية']);

$modulesRows = $pdo->query('SELECT id,key_name FROM modules')->fetchAll();
$pmStmt = $pdo->prepare('INSERT INTO plan_modules (plan_id,module_id,enabled) VALUES (?,?,?) ON DUPLICATE KEY UPDATE enabled=VALUES(enabled)');
foreach ($modulesRows as $module) {
    $small = in_array($module['key_name'], ['inbound_documents','outbound_documents','attachments','search'], true) ? 1 : 0;
    $medium = in_array($module['key_name'], ['backup_restore'], true) ? 0 : 1;
    $pmStmt->execute([$smallPlanId, (int) $module['id'], $small]);
    $pmStmt->execute([$mediumPlanId, (int) $module['id'], $medium]);
    $pmStmt->execute([$enterprisePlanId, (int) $module['id'], 1]);
}

echo "[OK] Seed completed successfully.\n";
echo "[CREDENTIALS] Dev: superadmin / superadmin123\n";
echo "[CREDENTIALS] Tenant: admin / admin123\n";
