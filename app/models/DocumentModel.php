<?php

declare(strict_types=1);

namespace App\Models;

class DocumentModel extends BaseModel
{
    public function paginateByType(int $tenantId, string $type): array
    {
        $stmt = $this->pdo->prepare('SELECT d.*, dep.name AS department_name FROM documents d LEFT JOIN departments dep ON dep.id=d.department_id WHERE d.tenant_id=? AND d.type=? ORDER BY d.id DESC LIMIT 100');
        $stmt->execute([$tenantId, $type]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO documents (tenant_id,type,doc_number,received_date,sent_date,sender_entity,receiver_entity,subject,priority,confidentiality,status,department_id,created_by,notes,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $data['tenant_id'], $data['type'], $data['doc_number'], $data['received_date'] ?: null, $data['sent_date'] ?: null,
            $data['sender_entity'] ?: null, $data['receiver_entity'] ?: null, $data['subject'], $data['priority'],
            $data['confidentiality'], $data['status'], $data['department_id'] ?: null, $data['created_by'], $data['notes'] ?: null,
            date('Y-m-d H:i:s'), date('Y-m-d H:i:s')
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function find(int $tenantId, int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM documents WHERE tenant_id=? AND id=? LIMIT 1');
        $stmt->execute([$tenantId, $id]);
        $doc = $stmt->fetch();
        return $doc ?: null;
    }

    public function attachments(int $tenantId, int $documentId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM document_attachments WHERE tenant_id=? AND document_id=? ORDER BY id DESC');
        $stmt->execute([$tenantId, $documentId]);
        return $stmt->fetchAll();
    }

    public function addAttachment(int $tenantId, int $docId, array $file): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO document_attachments (tenant_id,document_id,file_path,original_name,mime,size,created_at) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$tenantId, $docId, $file['file_path'], $file['original_name'], $file['mime'], $file['size'], date('Y-m-d H:i:s')]);
    }

    public function search(int $tenantId, array $filters): array
    {
        $sql = 'SELECT * FROM documents WHERE tenant_id = :tenant_id';
        $params = ['tenant_id' => $tenantId];
        foreach (['doc_number','subject','status','confidentiality'] as $f) {
            if (!empty($filters[$f])) {
                $sql .= " AND {$f} LIKE :{$f}";
                $params[$f] = '%' . $filters[$f] . '%';
            }
        }
        if (!empty($filters['date_from'])) {
            $sql .= ' AND DATE(COALESCE(received_date,sent_date)) >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= ' AND DATE(COALESCE(received_date,sent_date)) <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }
        $sql .= ' ORDER BY id DESC LIMIT 200';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function dashboardStats(int $tenantId): array
    {
        $stats = [];
        $queries = [
            'total' => 'SELECT COUNT(*) FROM documents WHERE tenant_id=?',
            'inbound' => "SELECT COUNT(*) FROM documents WHERE tenant_id=? AND type='inbound'",
            'outbound' => "SELECT COUNT(*) FROM documents WHERE tenant_id=? AND type='outbound'",
            'today' => 'SELECT COUNT(*) FROM documents WHERE tenant_id=? AND DATE(created_at)=CURDATE()',
        ];
        foreach ($queries as $key => $sql) {
            $st = $this->pdo->prepare($sql);
            $st->execute([$tenantId]);
            $stats[$key] = (int) $st->fetchColumn();
        }
        $stats['monthly'] = $this->pdo->query("SELECT DATE_FORMAT(created_at,'%Y-%m') m, COUNT(*) c FROM documents WHERE tenant_id={$tenantId} GROUP BY m ORDER BY m DESC LIMIT 12")->fetchAll();
        return $stats;
    }
}
