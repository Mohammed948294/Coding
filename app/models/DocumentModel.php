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

    public function update(array $data): void
    {
        $stmt = $this->pdo->prepare('UPDATE documents SET subject=?, status=?, priority=?, confidentiality=?, notes=?, updated_at=? WHERE tenant_id=? AND id=?');
        $stmt->execute([
            $data['subject'], $data['status'], $data['priority'], $data['confidentiality'], $data['notes'], date('Y-m-d H:i:s'),
            $data['tenant_id'], $data['id']
        ]);
    }

    public function archive(int $tenantId, int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE documents SET status='archived', updated_at=? WHERE tenant_id=? AND id=?");
        $stmt->execute([date('Y-m-d H:i:s'), $tenantId, $id]);
    }

    public function find(int $tenantId, int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM documents WHERE tenant_id=? AND id=? LIMIT 1');
        $stmt->execute([$tenantId, $id]);
        $doc = $stmt->fetch();
        return $doc ?: null;
    }

    public function inboundList(int $tenantId): array
    {
        $stmt = $this->pdo->prepare("SELECT id, doc_number, subject FROM documents WHERE tenant_id=? AND type='inbound' ORDER BY id DESC LIMIT 100");
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public function linkDocument(int $tenantId, int $documentId, int $linkedId, string $relationType): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO document_links (tenant_id, document_id, linked_document_id, relation_type) VALUES (?,?,?,?)');
        $stmt->execute([$tenantId, $documentId, $linkedId, $relationType]);
    }

    public function links(int $tenantId, int $documentId): array
    {
        $stmt = $this->pdo->prepare('SELECT dl.*, d.doc_number, d.subject FROM document_links dl JOIN documents d ON d.id = dl.linked_document_id WHERE dl.tenant_id=? AND dl.document_id=?');
        $stmt->execute([$tenantId, $documentId]);
        return $stmt->fetchAll();
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
        if (!empty($filters['type'])) {
            $sql .= ' AND type = :type';
            $params['type'] = $filters['type'];
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

        $stmt = $this->pdo->prepare("SELECT DATE_FORMAT(created_at,'%Y-%m') m, COUNT(*) c FROM documents WHERE tenant_id=? GROUP BY m ORDER BY m DESC LIMIT 12");
        $stmt->execute([$tenantId]);
        $stats['monthly'] = $stmt->fetchAll();

        return $stats;
    }
}
