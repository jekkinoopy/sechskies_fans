<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();

$key = (string) ($_GET['module'] ?? $_POST['module'] ?? '');
$module = module_config($key);
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$record = [];
if ($id) {
    $stmt = db()->prepare('SELECT * FROM `' . $module['table'] . '` WHERE id = ?');
    $stmt->execute([$id]);
    $record = $stmt->fetch() ?: [];
    if (!$record) { http_response_code(404); exit('資料不存在。'); }
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $data = [];
    foreach ($module['fields'] as $name => $field) {
        if ($field['type'] === 'file') continue;
        $value = $field['type'] === 'checkbox' ? (isset($_POST[$name]) ? 1 : 0) : trim((string) ($_POST[$name] ?? ''));
        if (!empty($field['required']) && $value === '') $error = '請完成所有必填欄位。';
        $data[$name] = $value === '' ? null : $value;
    }
    if ($key === 'media' && isset($_FILES['file_path']) && $_FILES['file_path']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['file_path'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp', 'application/pdf' => 'pdf', 'video/mp4' => 'mp4'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > $config['max_upload_bytes'] || !isset($allowed[$mime])) {
            $error = '檔案格式不支援或超過 8 MB。';
        } else {
            if (!is_dir($config['upload_dir'])) mkdir($config['upload_dir'], 0755, true);
            $safeName = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
            if (!move_uploaded_file($file['tmp_name'], $config['upload_dir'] . '/' . $safeName)) {
                $error = '檔案儲存失敗。';
            } else {
                $data['file_path'] = $config['upload_url'] . '/' . $safeName;
                $data['original_name'] = $file['name'];
                $data['mime_type'] = $mime;
                $data['file_size'] = $file['size'];
            }
        }
    } elseif ($key === 'media' && $id) {
        $data['file_path'] = $record['file_path'];
    } elseif ($key === 'media') {
        $error = '新增媒體時必須選擇檔案。';
    }
    if ($error === '') {
        if ($id) {
            $sets = [];
            foreach ($data as $name => $_) $sets[] = "`$name` = ?";
            $stmt = db()->prepare('UPDATE `' . $module['table'] . '` SET ' . implode(', ', $sets) . ' WHERE id = ?');
            $stmt->execute([...array_values($data), $id]);
            audit('update', $module['table'], $id, $data);
            flash('success', '資料已更新。');
        } else {
            $names = array_keys($data);
            $stmt = db()->prepare('INSERT INTO `' . $module['table'] . '` (`' . implode('`,`', $names) . '`) VALUES (' . implode(',', array_fill(0, count($names), '?')) . ')');
            $stmt->execute(array_values($data));
            $id = (int) db()->lastInsertId();
            audit('create', $module['table'], $id, $data);
            flash('success', '資料已新增。');
        }
        redirect('records.php?module=' . urlencode($key));
    }
    $record = array_merge($record, $data);
}
render_header(($id ? '編輯' : '新增') . ($module['item_label'] ?? $module['label']), $key);
?>
<?php if ($error): ?><div class="notice notice--error"><?= h($error) ?></div><?php endif; ?>
<form class="panel editor" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="module" value="<?= h($key) ?>"><input type="hidden" name="id" value="<?= $id ?>"><div class="form-grid">
<?php foreach ($module['fields'] as $name => $field): $value = $record[$name] ?? ($field['default'] ?? ''); ?><label class="field <?= $field['type'] === 'textarea' ? 'field--wide' : '' ?>"><span><?= h($field['label']) ?><?= !empty($field['required']) ? ' *' : '' ?></span>
<?php if ($field['type'] === 'textarea'): ?><textarea name="<?= h($name) ?>" rows="6" <?= !empty($field['required']) ? 'required' : '' ?>><?= h((string) $value) ?></textarea>
<?php elseif ($field['type'] === 'select'): ?><select name="<?= h($name) ?>" <?= !empty($field['required']) ? 'required' : '' ?>><option value="">請選擇</option><?php foreach ($field['options'] as $optionValue => $optionLabel): ?><option value="<?= h($optionValue) ?>" <?= (string) $value === (string) $optionValue ? 'selected' : '' ?>><?= h($optionLabel) ?></option><?php endforeach; ?></select>
<?php elseif ($field['type'] === 'status'): ?><select name="<?= h($name) ?>" required><?php foreach (['draft' => '草稿', 'coming_soon' => '籌備中', 'published' => '公開', 'archived' => '封存'] as $optionValue => $optionLabel): ?><option value="<?= $optionValue ?>" <?= (string) ($value ?: 'draft') === $optionValue ? 'selected' : '' ?>><?= $optionLabel ?></option><?php endforeach; ?></select>
<?php elseif ($field['type'] === 'relation'): ?><select name="<?= h($name) ?>" required><option value="">請選擇</option><?php foreach (db()->query("SELECT id, title FROM `{$field['table']}` ORDER BY title")->fetchAll() as $option): ?><option value="<?= (int) $option['id'] ?>" <?= (string) $value === (string) $option['id'] ? 'selected' : '' ?>><?= h($option['title']) ?></option><?php endforeach; ?></select>
<?php elseif ($field['type'] === 'checkbox'): ?><span class="check"><input type="checkbox" name="<?= h($name) ?>" value="1" <?= $value ? 'checked' : '' ?>> 是</span>
<?php elseif ($field['type'] === 'file'): ?><?php if ($value): ?><small>目前檔案：<?= h((string) $value) ?></small><?php endif; ?><input type="file" name="<?= h($name) ?>" accept="image/*,video/mp4,application/pdf" <?= !$id ? 'required' : '' ?>>
<?php else: ?><input type="<?= h($field['type']) ?>" name="<?= h($name) ?>" value="<?= h((string) $value) ?>" <?= isset($field['step']) ? 'step="' . h($field['step']) . '"' : '' ?> <?= !empty($field['required']) ? 'required' : '' ?>><?php endif; ?></label><?php endforeach; ?></div>
<div class="form-actions"><a class="button" href="records.php?module=<?= h($key) ?>">取消</a><button class="button button--primary" type="submit"><?= $id ? '儲存修改' : '建立資料' ?></button></div></form>
<?php render_footer();
