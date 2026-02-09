<?php

declare(strict_types=1);

namespace Core;

use RuntimeException;

final class FileStorage
{
    public static function saveUpload(array $file, int $tenantId): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed');
        }

        $maxSize = (int) AppContext::config('app.max_upload_size');
        if (($file['size'] ?? 0) > $maxSize) {
            throw new RuntimeException('File too large');
        }

        $mime = mime_content_type($file['tmp_name']);
        $allowed = AppContext::config('app.allowed_mimes', []);
        if (!in_array($mime, $allowed, true)) {
            throw new RuntimeException('Invalid file type');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $name = Helpers::randomFileName($ext);
        $tenantDir = rtrim(AppContext::config('app.upload_dir'), '/') . '/' . $tenantId;
        if (!is_dir($tenantDir)) {
            mkdir($tenantDir, 0775, true);
        }

        $target = $tenantDir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new RuntimeException('Failed to move upload');
        }

        return [
            'file_path' => $tenantId . '/' . $name,
            'original_name' => $file['name'],
            'mime' => $mime,
            'size' => (int) $file['size'],
            'virus_scan_note' => 'Virus scanning integration stub: integrate ClamAV in production.',
        ];
    }
}
