<?php

declare(strict_types=1);

namespace Dev\Models;

class TenantModel extends BaseModel
{
    public function all(): array
    {
        return $this->pdo->query('SELECT t.*, p.name plan_name FROM tenants t LEFT JOIN plans p ON p.id=t.plan_id ORDER BY t.id DESC')->fetchAll();
    }

    public function plans(): array
    {
        return $this->pdo->query('SELECT * FROM plans ORDER BY id')->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO tenants (name,slug,plan_id,is_active,created_at) VALUES (?,?,?,?,?)');
        $stmt->execute([$data['name'],$data['slug'],$data['plan_id'],1,date('Y-m-d H:i:s')]);
        return (int) $this->pdo->lastInsertId();
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
        $id = $stmt->fetchColumn();
        return (int) $id;
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

    public function healthSummary(): array
    {
        $tenants = (int) $this->pdo->query('SELECT COUNT(*) FROM tenants')->fetchColumn();
        $docs = (int) $this->pdo->query('SELECT COUNT(*) FROM documents')->fetchColumn();
        $logs = (int) $this->pdo->query('SELECT COUNT(*) FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)')->fetchColumn();
        return compact('tenants','docs','logs');
    }
}
