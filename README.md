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
1. استورد `database/schema.sql`.
2. عدّل DB credentials في `core/config.php`.
3. شغّل seeder:
   ```bash
   php database/seed.php
   ```
4. شغّل السيرفر:
   ```bash
   php -S localhost:8000 -t .
   ```
5. الروابط:
   - Client: `http://localhost:8000/app/public/index.php`
   - Dev: `http://localhost:8000/dev/public/index.php`

## Default Credentials
- Dev Superadmin:
  - username: `devadmin`
  - password: `Dev@1234`
- Demo Tenant Admin:
  - username: `admin`
  - password: `Admin@1234`

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
