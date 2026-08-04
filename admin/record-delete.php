<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
verify_csrf();
$key = (string) ($_POST['module'] ?? '');
$module = module_config($key);
$id = (int) ($_POST['id'] ?? 0);
$stmt = db()->prepare('DELETE FROM `' . $module['table'] . '` WHERE id = ?');
$stmt->execute([$id]);
audit('delete', $module['table'], $id);
flash('success', '資料已刪除。');
redirect('records.php?module=' . urlencode($key));
