<?php use Core\Auth; use Core\FeatureGate; $u=Auth::appUser(); $tenantId=(int)$u['tenant_id']; ?>
<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet"><title>الأرشيف</title></head>
<body class="bg-light"><div class="d-flex"><aside class="bg-dark text-white p-3" style="width:260px;min-height:100vh"><h5>نظام الأرشيف</h5><ul class="nav flex-column gap-2">
<li><a class="nav-link text-white" href="/app/public/index.php?r=dashboard">لوحة التحكم</a></li>
<?php if (FeatureGate::isModuleEnabled($tenantId,'inbound')): ?><li><a class="nav-link text-white" href="/app/public/index.php?r=inbound.index">الوارد</a></li><?php endif; ?>
<?php if (FeatureGate::isModuleEnabled($tenantId,'outbound')): ?><li><a class="nav-link text-white" href="/app/public/index.php?r=outbound.index">الصادر</a></li><?php endif; ?>
<?php if (FeatureGate::isModuleEnabled($tenantId,'archive')): ?><li><a class="nav-link text-white" href="/app/public/index.php?r=search">البحث</a></li><?php endif; ?>
<?php if (FeatureGate::isModuleEnabled($tenantId,'user_management')): ?><li><a class="nav-link text-white" href="/app/public/index.php?r=users.index">المستخدمون</a></li><?php endif; ?>
<li><a class="nav-link text-warning" href="/app/public/index.php?r=logout">خروج</a></li></ul></aside><main class="flex-grow-1 p-4"><?php include $this->viewPath($viewFile); ?></main></div></body></html>
