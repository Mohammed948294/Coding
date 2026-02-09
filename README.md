# E-Archive Management System (Multi-Tenant) + Developer Control Panel

نظام أرشفة مؤسسي جاهز للبيع للمؤسسات، مبني بـ **PHP 8.2 Vanilla MVC** (بدون Laravel/Symfony) مع فصل صارم بين:

- **Client App**: `/app/public/index.php` (للعميل/المؤسسة)
- **Developer Panel**: `/dev/public/index.php` (خاص بالمطور فقط)

## Dev vs Client Separation (مهم)
- أي كود/واجهة إدارة عامة موجودة في `dev/` فقط.
- أي كود/واجهة تشغيل للمؤسسة موجودة في `app/` فقط.
- جلسات دخول المطور `dev_user` منفصلة بالكامل عن جلسات العميل `app_user`.
- لا توجد روابط dev داخل واجهات العميل.

## Features
- Multi-tenant tenant_id scoping على كل البيانات.
- Feature toggles per tenant (من لوحة المطور فقط).
- Plans: Small / Medium / Enterprise + plan defaults + tenant override.
- Tenant limits: users, storage, documents.
- Theme/UI controls per tenant: light/dark, primary color, sidebar modules, dashboard widgets.
- RBAC + CSRF + prepared statements + audit logs.
- Secure attachments خارج public مع تنزيل عبر controller.

## Folder Structure
```
/app
/dev
/core
/storage/uploads
/database/schema.sql
/database/seed.php
```

## Setup
1. حدّث اتصال قاعدة البيانات في `core/config.php` (الافتراضي: `e_archive`).
2. شغّل seeder (سيُنشئ القاعدة والجداول تلقائيًا إذا كانت ناقصة):
   ```bash
   php database/seed.php
   ```
3. شغّل السيرفر:
   ```bash
   php -S localhost:8000 -t .
   ```
4. الروابط:
   - Client: `http://localhost:8000/app/public/index.php?r=login`
   - Dev: `http://localhost:8000/dev/public/index.php?r=login`

## Default Credentials (Working)
- Dev Superadmin:
  - username: `superadmin`
  - password: `superadmin123`
- Demo Tenant Admin:
  - username: `admin`
  - password: `admin123`

## Feature Management Workflow
1. ادخل لوحة المطور.
2. أنشئ Tenant أو عدّل Tenant قائم.
3. حدّد الخطة (Plan).
4. عدّل overrides من صفحة الوحدات الخاصة بالمؤسسة.
5. غيّر إعدادات الواجهة (Sidebar + Widgets + Theme).

## Security Notes
- كل form حساس يحتوي CSRF token.
- جميع الاستعلامات Prepared Statements.
- tenant scoping إلزامي في الاستعلامات الأساسية.
- المرفقات محفوظة في `storage/uploads` وغير مكشوفة من الويب مباشرة.
- Audit logs لكل عمليات login/create/update/module changes.

## Production Notes
- فعّل HTTPS.
- اضبط session secure flags.
- اجعل `storage/uploads` writable فقط لسيرفر الويب.
- أضف Virus Scanner حقيقي (ClamAV) مكان الـ stub.
- يُفضل reverse proxy + process manager (Nginx + PHP-FPM).
