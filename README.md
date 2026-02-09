# E-Archive Management System (Multi-Tenant) + Developer Control Panel

نظام أرشفة إلكترونية متعدد المؤسسات (Inbound/Outbound) مبني بـ PHP 8.2 (Vanilla MVC) مع لوحتين:
- **Client App**: `/app/public/index.php`
- **Developer Panel**: `/dev/public/index.php`

## Highlights
- Multi-tenant strict scoping باستخدام `tenant_id` + `TenantContext`.
- خطط وباقات + Feature Flags (`modules`, `plans`, `plan_modules`, `tenant_modules`).
- Secure auth (sessions + `password_hash`/`password_verify`).
- CSRF على كل النماذج.
- RBAC permissions.
- Audit logs للأحداث الحساسة.
- رفع ملفات آمن إلى `storage/uploads` خارج public، والتنزيل عبر controller فقط.

## Folder Structure
```
/app
  /public
  /controllers
  /models
  /views
  /middlewares
/dev
  /public
  /controllers
  /models
  /views
  /middlewares
/core
/storage/uploads
/database/schema.sql
/database/seed.php
```

## Setup
1. أنشئ قاعدة بيانات MySQL واستورد:
   - `database/schema.sql`
2. عدّل اتصال DB في:
   - `core/config.php`
3. شغّل seeder:
   ```bash
   php database/seed.php
   ```
4. شغّل السيرفر المحلي:
   ```bash
   php -S localhost:8000 -t .
   ```
5. افتح:
   - Client: `http://localhost:8000/app/public/index.php`
   - Dev: `http://localhost:8000/dev/public/index.php`

## Default Accounts
- Developer superadmin:
  - username: `devadmin`
  - password: `Dev@1234`
- Demo tenant admin:
  - username: `admin`
  - password: `Admin@1234`

## Module / Plan Management
- من لوحة المطور: المؤسسات -> إدارة الوحدات.
- fallback behavior:
  - إن لم يوجد override في `tenant_modules` يتم استخدام `plan_modules`.
- الإخفاء في الواجهة + الحماية في route middleware.

## Security Notes
- كل الاستعلامات عبر PDO prepared statements.
- منع الوصول المباشر للمرفقات؛ المسار الحقيقي خارج `public`.
- التحقق من MIME والحجم والامتداد + اسم عشوائي للملف.
- سجل تدقيق `audit_logs` لجميع logins وعمليات create/update الحرجة.
- ملاحظة فحص فيروسات (stub) جاهزة للربط مع ClamAV.

## Production Notes
- فعّل HTTPS و`session.cookie_secure`.
- ضع صلاحيات مجلد `storage/uploads` لتكون writable فقط من السيرفر.
- أضف rate-limiting وcentralized logging (syslog/ELK) في البيئة الإنتاجية.
- استخدم cron للنسخ الاحتياطي الدوري (module: backup_restore).
