<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DocumentModel;
use Core\Audit;
use Core\Auth;
use Core\CSRF;
use Core\Database;
use Core\FeatureGate;
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
        $inboundRefs = (new DocumentModel())->inboundList($tenantId);

        $this->render('documents/create', [
            'type' => $type,
            'departments' => $departments,
            'inboundRefs' => $inboundRefs,
            'csrf' => CSRF::token('doc_create')
        ]);
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

        if ($type === 'outbound' && !empty($_POST['inbound_reference_id'])) {
            $model->linkDocument($tenantId, $id, (int) $_POST['inbound_reference_id'], 'references_inbound');
        }

        if (FeatureGate::isModuleEnabled($tenantId, 'attachments') && !empty($_FILES['attachments']['name'][0])) {
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

    public function editForm(int $id): void
    {
        $tenantId = (int) Auth::appUser()['tenant_id'];
        $document = (new DocumentModel())->find($tenantId, $id);
        if (!$document) {
            exit('Not found');
        }
        $this->render('documents/edit', ['document' => $document, 'csrf' => CSRF::token('doc_edit')]);
    }

    public function update(int $id): void
    {
        if (!CSRF::verify($_POST['_csrf'] ?? '', 'doc_edit')) {
            exit('Invalid CSRF');
        }
        $tenantId = (int) Auth::appUser()['tenant_id'];
        (new DocumentModel())->update([
            'id' => $id,
            'tenant_id' => $tenantId,
            'subject' => Validator::sanitize($_POST['subject'] ?? ''),
            'status' => $_POST['status'] ?? 'new',
            'priority' => $_POST['priority'] ?? 'normal',
            'confidentiality' => $_POST['confidentiality'] ?? 'normal',
            'notes' => Validator::sanitize($_POST['notes'] ?? ''),
        ]);
        Audit::log('user', (int)Auth::appUser()['id'], $tenantId, 'update', 'document', $id);
        $this->redirect('/app/public/index.php?r=documents.show&id=' . $id);
    }

    public function archive(int $id): void
    {
        if (!CSRF::verify($_POST['_csrf'] ?? '', 'doc_archive')) {
            exit('Invalid CSRF');
        }
        $tenantId = (int) Auth::appUser()['tenant_id'];
        (new DocumentModel())->archive($tenantId, $id);
        Audit::log('user', (int)Auth::appUser()['id'], $tenantId, 'archive', 'document', $id);
        $this->redirect('/app/public/index.php?r=documents.show&id=' . $id);
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
        $links = $model->links($tenantId, $id);
        $this->render('documents/show', ['document' => $document, 'attachments' => $attachments, 'links' => $links, 'archiveCsrf' => CSRF::token('doc_archive')]);
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
