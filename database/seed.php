<?php

declare(strict_types=1);

$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=earchive;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$now = date('Y-m-d H:i:s');

$permissions = [
    'inbound.view','inbound.create','inbound.edit','inbound.delete',
    'outbound.view','outbound.create','outbound.edit','outbound.delete',
    'documents.download','users.manage','roles.manage','settings.manage','reports.view','reports.export'
];
foreach ($permissions as $perm) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO permissions (key_name,description) VALUES (?,?)');
    $stmt->execute([$perm, $perm]);
}

$modules = ['inbound','outbound','archive','advanced_search','barcode_qr','workflow','digital_signature_stub','notifications','reports','user_management','settings','api_access','backup_restore','integrations'];
foreach ($modules as $m) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO modules (name,key_name,is_active) VALUES (?,?,1)');
    $stmt->execute([ucwords(str_replace('_',' ',$m)), $m]);
}

$plans = ['Small','Medium','Enterprise'];
foreach ($plans as $pl) {
    $stmt = $pdo->prepare('INSERT IGNORE INTO plans (name,description) VALUES (?,?)');
    $stmt->execute([$pl, $pl . ' Plan']);
}

$devPass = password_hash('Dev@1234', PASSWORD_DEFAULT);
$pdo->prepare('INSERT IGNORE INTO dev_users (username,password_hash,full_name,is_active,created_at) VALUES (?,?,?,?,?)')
    ->execute(['devadmin',$devPass,'Developer Super Admin',1,$now]);

$smallPlanId = (int)$pdo->query("SELECT id FROM plans WHERE name='Small'")->fetchColumn();
$mediumPlanId = (int)$pdo->query("SELECT id FROM plans WHERE name='Medium'")->fetchColumn();
$entPlanId = (int)$pdo->query("SELECT id FROM plans WHERE name='Enterprise'")->fetchColumn();

$pdo->prepare('INSERT IGNORE INTO tenants (id,name,slug,plan_id,is_active,created_at) VALUES (?,?,?,?,?,?)')
    ->execute([1,'Demo Org','demo-org',$mediumPlanId,1,$now]);

$roleStmt = $pdo->prepare('INSERT IGNORE INTO roles (tenant_id,name,key_name,created_at) VALUES (?,?,?,?)');
$roleStmt->execute([1,'Tenant Super Admin','tenant_super_admin',$now]);
$roleStmt->execute([1,'Officer','officer',$now]);
$superRoleId = (int)$pdo->query("SELECT id FROM roles WHERE tenant_id=1 AND key_name='tenant_super_admin'")->fetchColumn();

$allPermissionIds = $pdo->query('SELECT id FROM permissions')->fetchAll(PDO::FETCH_COLUMN);
$rp = $pdo->prepare('INSERT IGNORE INTO role_permissions (role_id,permission_id) VALUES (?,?)');
foreach ($allPermissionIds as $pid) {
    $rp->execute([$superRoleId, (int)$pid]);
}

$adminPass = password_hash('Admin@1234', PASSWORD_DEFAULT);
$pdo->prepare('INSERT IGNORE INTO users (tenant_id,role_id,username,password_hash,full_name,is_active,created_at) VALUES (?,?,?,?,?,?,?)')
    ->execute([1,$superRoleId,'admin',$adminPass,'Tenant Admin',1,$now]);

$pdo->prepare('INSERT IGNORE INTO departments (tenant_id,name) VALUES (1,?),(1,?),(1,?)')->execute(['الإدارة العامة','الموارد البشرية','المالية']);

$moduleRows = $pdo->query('SELECT id,key_name FROM modules')->fetchAll(PDO::FETCH_ASSOC);
$insPM = $pdo->prepare('INSERT IGNORE INTO plan_modules (plan_id,module_id,enabled) VALUES (?,?,?)');
foreach ($moduleRows as $m) {
    $insPM->execute([$smallPlanId, (int)$m['id'], in_array($m['key_name'], ['inbound','outbound','archive'], true) ? 1 : 0]);
    $insPM->execute([$mediumPlanId, (int)$m['id'], in_array($m['key_name'], ['backup_restore','integrations'], true) ? 0 : 1]);
    $insPM->execute([$entPlanId, (int)$m['id'], 1]);
}

echo "Seeding completed\n";
