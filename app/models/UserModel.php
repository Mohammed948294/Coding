<?php

declare(strict_types=1);

namespace App\Models;

class UserModel extends BaseModel
{
    public function listUsers(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT u.id,u.username,u.full_name,r.name role_name,u.is_active FROM users u JOIN roles r ON r.id=u.role_id WHERE u.tenant_id=? ORDER BY u.id DESC');
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public function roles(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM roles WHERE tenant_id=?');
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (tenant_id,role_id,username,password_hash,full_name,is_active,created_at) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$data['tenant_id'],$data['role_id'],$data['username'],password_hash($data['password'], PASSWORD_DEFAULT),$data['full_name'],1,date('Y-m-d H:i:s')]);
    }
}
