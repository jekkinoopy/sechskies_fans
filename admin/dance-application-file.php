<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT reference_file_name, reference_original_name, reference_mime_type FROM dance_applications WHERE id = ?');
$stmt->execute([$id]);
$file = $stmt->fetch();
if (!$file || !$file['reference_file_name']) {
    http_response_code(404);
    exit('找不到檔案。');
}

$storedName = basename((string) $file['reference_file_name']);
$path = (string) $config['dance_upload_dir'] . DIRECTORY_SEPARATOR . $storedName;
if (!is_file($path)) {
    http_response_code(404);
    exit('檔案不存在。');
}

$downloadName = basename((string) ($file['reference_original_name'] ?: $storedName));
$downloadName = str_replace(["\r", "\n", '"'], '', $downloadName);
audit('download', 'dance_applications', $id, ['file' => $downloadName]);

header('Content-Type: ' . ((string) $file['reference_mime_type'] ?: 'application/octet-stream'));
header('Content-Length: ' . filesize($path));
header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($downloadName));
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
