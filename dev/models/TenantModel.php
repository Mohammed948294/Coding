<?php

declare(strict_types=1);

namespace Dev\Models;

class TenantModel extends BaseModel
{
    public function all(): array
    {
        return $this->pdo->query('SELECT t.*, p.name plan_name, ts.max_users, ts.max_storage_mb, ts.max_documents, ts.theme_mode FROM tenants t LEFT JOIN plans p ON p.id=t.plan_id LEFT JOIN tenant_settings ts ON ts.tenant_id=t.id ORDER BY t.id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT t.*, ts.* FROM tenants t LEFT JOIN tenant_settings ts ON ts.tenant_id=t.id WHERE t.id=? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function plans(): array
    {
        return $this->pdo->query('SELECT * FROM plans ORDER BY id')->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO tenants (name,slug,plan_id,is_active,created_at) VALUES (?,?,?,?,?)');
        $stmt->execute([$data['name'],$data['slug'],$data['plan_id'],1,date('Y-m-d H:i:s')]);
        $id = (int) $this->pdo->lastInsertId();
        $this->upsertSettings($id, $data);
        $this->seedRoles($id);
        return $id;
    }

    public function updateTenant(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare('UPDATE tenants SET name=?, slug=?, plan_id=?, is_active=? WHERE id=?');
        $stmt->execute([$data['name'], $data['slug'], $data['plan_id'], $data['is_active'], $id]);
        $this->upsertSettings($id, $data);
    }

    private function upsertSettings(int $tenantId, array $data): void
    {
        $sidebar = json_encode($data['sidebar_modules'] ?? ['dashboard','inbound_documents','outbound_documents','search','multi_users'], JSON_UNESCAPED_UNICODE);
        $widgets = json_encode($data['dashboard_widgets'] ?? ['total','inbound','outbound','today','activities'], JSON_UNESCAPED_UNICODE);

        $stmt = $this->pdo->prepare('INSERT INTO tenant_settings (tenant_id, organization_name, numbering_pattern, default_confidentiality, max_users, max_storage_mb, max_documents, theme_mode, primary_color, sidebar_modules_json, dashboard_widgets_json, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE organization_name=VALUES(organization_name), numbering_pattern=VALUES(numbering_pattern), default_confidentiality=VALUES(default_confidentiality), max_users=VALUES(max_users), max_storage_mb=VALUES(max_storage_mb), max_documents=VALUES(max_documents), theme_mode=VALUES(theme_mode), primary_color=VALUES(primary_color), sidebar_modules_json=VALUES(sidebar_modules_json), dashboard_widgets_json=VALUES(dashboard_widgets_json), updated_at=VALUES(updated_at)');
        $stmt->execute([
            $tenantId,
            $data['organization_name'] ?? $data['name'] ?? null,
            $data['numbering_pattern'] ?? 'DOC-{Y}-{SEQ}',
            $data['default_confidentiality'] ?? 'normal',
            (int)($data['max_users'] ?? 25),
            (int)($data['max_storage_mb'] ?? 1024),
            (int)($data['max_documents'] ?? 50000),
            $data['theme_mode'] ?? 'light',
            $data['primary_color'] ?? '#0d6efd',
            $sidebar,
            $widgets,
            date('Y-m-d H:i:s'),
        ]);
    }

    private function seedRoles(int $tenantId): void
    {
        $stmt = $this->pdo->prepare('INSERT IGNORE INTO roles (tenant_id,name,key_name,created_at) VALUES (?,?,?,?)');
        $stmt->execute([$tenantId, 'Tenant Super Admin', 'tenant_super_admin', date('Y-m-d H:i:s')]);
        $stmt->execute([$tenantId, 'Staff', 'staff', date('Y-m-d H:i:s')]);
    }

    public function createAdmin(array $data): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (tenant_id,role_id,username,password_hash,full_name,is_active,created_at) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$data['tenant_id'],$data['role_id'],$data['username'],password_hash($data['password'], PASSWORD_DEFAULT),$data['full_name'],1,date('Y-m-d H:i:s')]);
    }

    public function roleSuperAdmin(int $tenantId): int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM roles WHERE tenant_id=? AND key_name='tenant_super_admin' LIMIT 1");
        $stmt->execute([$tenantId]);
        return (int) $stmt->fetchColumn();
    }

    public function modules(): array
    {
        return $this->pdo->query('SELECT * FROM modules ORDER BY name')->fetchAll();
    }

    public function tenantModuleMap(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT module_id, enabled FROM tenant_modules WHERE tenant_id=?');
        $stmt->execute([$tenantId]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[(int)$row['module_id']] = (int)$row['enabled'];
        }
        return $out;
    }

    public function setOverride(int $tenantId, int $moduleId, int $enabled): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO tenant_modules (tenant_id,module_id,enabled) VALUES (?,?,?) ON DUPLICATE KEY UPDATE enabled=VALUES(enabled)');
        $stmt->execute([$tenantId,$moduleId,$enabled]);
    }

    public function storageByTenant(): array
    {
        $sql = 'SELECT t.id, t.name, COALESCE(SUM(da.size),0) bytes FROM tenants t LEFT JOIN document_attachments da ON da.tenant_id=t.id GROUP BY t.id, t.name ORDER BY t.id DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function healthSummary(): array
    {
        $tenants = (int) $this->pdo->query('SELECT COUNT(*) FROM tenants')->fetchColumn();
        $docs = (int) $this->pdo->query('SELECT COUNT(*) FROM documents')->fetchColumn();
        $logs = (int) $this->pdo->query('SELECT COUNT(*) FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)')->fetchColumn();
        return compact('tenants','docs','logs');
    }
}
