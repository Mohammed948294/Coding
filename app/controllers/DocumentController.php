<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DocumentModel;
use Core\Audit;
use Core\Auth;
use Core\CSRF;
use Core\Database;
use Core\FileStorage;
use Core\Validator;

final class DocumentController extends BaseController
{
    public function index(string $type): void
    {
        $tenantId = (int) Auth::appUser()['tenant_id'];
        $documents = (new DocumentModel())->paginateByType($tenantId, $type);
        $this->render('documents/index', compact('documents', 'type'));
    }

    public function createForm(string $type): void
    {
        $tenantId = (int) Auth::appUser()['tenant_id'];
        $stmt = Database::pdo()->prepare('SELECT * FROM departments WHERE tenant_id=?');
        $stmt->execute([$tenantId]);
        $departments = $stmt->fetchAll();
        $this->render('documents/create', ['type' => $type, 'departments' => $departments, 'csrf' => CSRF::token('doc_create')]);
    }

    public function store(string $type): void
    {
        if (!CSRF::verify($_POST['_csrf'] ?? '', 'doc_create')) {
            exit('Invalid CSRF');
        }
        $errors = Validator::required($_POST, ['doc_number', 'subject', 'status']);
        if ($errors) {
            exit('Validation error');
        }
        $user = Auth::appUser();
        $tenantId = (int) $user['tenant_id'];
        $model = new DocumentModel();
        $id = $model->create([
            'tenant_id' => $tenantId,
            'type' => $type,
            'doc_number' => Validator::sanitize($_POST['doc_number']),
            'received_date' => $_POST['received_date'] ?? null,
            'sent_date' => $_POST['sent_date'] ?? null,
            'sender_entity' => Validator::sanitize($_POST['sender_entity'] ?? ''),
            'receiver_entity' => Validator::sanitize($_POST['receiver_entity'] ?? ''),
            'subject' => Validator::sanitize($_POST['subject']),
            'priority' => $_POST['priority'] ?? 'normal',
            'confidentiality' => $_POST['confidentiality'] ?? 'normal',
            'status' => $_POST['status'] ?? 'new',
            'department_id' => (int) ($_POST['department_id'] ?? 0),
            'created_by' => (int) $user['id'],
            'notes' => Validator::sanitize($_POST['notes'] ?? ''),
        ]);

        if (!empty($_FILES['attachments']['name'][0])) {
            foreach ($_FILES['attachments']['name'] as $i => $n) {
                $file = [
                    'name' => $_FILES['attachments']['name'][$i],
                    'type' => $_FILES['attachments']['type'][$i],
                    'tmp_name' => $_FILES['attachments']['tmp_name'][$i],
                    'error' => $_FILES['attachments']['error'][$i],
                    'size' => $_FILES['attachments']['size'][$i],
                ];
                $saved = FileStorage::saveUpload($file, $tenantId);
                $model->addAttachment($tenantId, $id, $saved);
            }
        }

        Audit::log('user', (int)$user['id'], $tenantId, 'create', 'document', $id);
        $this->redirect('/app/public/index.php?r=' . ($type === 'inbound' ? 'inbound.index' : 'outbound.index'));
    }

    public function show(int $id): void
    {
        $tenantId = (int) Auth::appUser()['tenant_id'];
        $model = new DocumentModel();
        $document = $model->find($tenantId, $id);
        if (!$document) {
            exit('Not found');
        }
        $attachments = $model->attachments($tenantId, $id);
        $this->render('documents/show', compact('document', 'attachments'));
    }

    public function download(int $attachmentId): void
    {
        $tenantId = (int) Auth::appUser()['tenant_id'];
        $stmt = Database::pdo()->prepare('SELECT * FROM document_attachments WHERE tenant_id=? AND id=? LIMIT 1');
        $stmt->execute([$tenantId, $attachmentId]);
        $row = $stmt->fetch();
        if (!$row) {
            exit('Not found');
        }
        $path = __DIR__ . '/../../storage/uploads/' . $row['file_path'];
        if (!file_exists($path)) {
            exit('Missing file');
        }
        header('Content-Type: ' . $row['mime']);
        header('Content-Disposition: attachment; filename="' . basename($row['original_name']) . '"');
        readfile($path);
        exit;
    }
}
