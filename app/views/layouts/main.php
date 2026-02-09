<?php
use Core\Auth;
use Core\Database;
use Core\FeatureGate;

$u = Auth::appUser();
$tenantId = (int) $u['tenant_id'];

$stmt = Database::pdo()->prepare('SELECT * FROM tenant_settings WHERE tenant_id=? LIMIT 1');
$stmt->execute([$tenantId]);
$settings = $stmt->fetch() ?: [];
$mode = $settings['theme_mode'] ?? 'light';
$primary = $settings['primary_color'] ?? '#0d6efd';
$sidebarModules = json_decode((string)($settings['sidebar_modules_json'] ?? ''), true);
if (!is_array($sidebarModules)) {
    $sidebarModules = ['dashboard','inbound_documents','outbound_documents','search','multi_users'];
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <title>الأرشيف الإلكتروني</title>
  <style>
    body { background: <?= $mode === 'dark' ? '#1f1f1f' : '#f7f8fa' ?>; color: <?= $mode === 'dark' ? '#f5f5f5' : '#202124' ?>; }
    .sidebar { width: 260px; min-height: 100vh; background: <?= $mode === 'dark' ? '#121212' : '#243447' ?>; }
    .btn-primary { background-color: <?= htmlspecialchars($primary) ?>; border-color: <?= htmlspecialchars($primary) ?>; }
    .card { border-radius: 12px; }
  </style>
</head>
<body>
<div class="d-flex">
  <aside class="sidebar text-white p-3">
    <h5 class="mb-3">نظام الأرشيف</h5>
    <ul class="nav flex-column gap-1">
      <?php if (in_array('dashboard', $sidebarModules, true)): ?><li><a class="nav-link text-white" href="/app/public/index.php?r=dashboard">لوحة التحكم</a></li><?php endif; ?>
      <?php if (in_array('inbound_documents', $sidebarModules, true) && FeatureGate::isModuleEnabled($tenantId,'inbound_documents')): ?><li><a class="nav-link text-white" href="/app/public/index.php?r=inbound.index">الوارد</a></li><?php endif; ?>
      <?php if (in_array('outbound_documents', $sidebarModules, true) && FeatureGate::isModuleEnabled($tenantId,'outbound_documents')): ?><li><a class="nav-link text-white" href="/app/public/index.php?r=outbound.index">الصادر</a></li><?php endif; ?>
      <?php if (in_array('search', $sidebarModules, true) && FeatureGate::isModuleEnabled($tenantId,'search')): ?><li><a class="nav-link text-white" href="/app/public/index.php?r=search">البحث</a></li><?php endif; ?>
      <?php if (in_array('multi_users', $sidebarModules, true) && FeatureGate::isModuleEnabled($tenantId,'multi_users')): ?><li><a class="nav-link text-white" href="/app/public/index.php?r=users.index">المستخدمون</a></li><?php endif; ?>
      <li><a class="nav-link text-warning" href="/app/public/index.php?r=logout">خروج</a></li>
    </ul>
  </aside>
  <main class="flex-grow-1 p-4"><?php include $this->viewPath($viewFile); ?></main>
</div>
</body>
</html>
