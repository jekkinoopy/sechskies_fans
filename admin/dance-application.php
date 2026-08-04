<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$statusOptions = dance_application_statuses();
$stmt = db()->prepare('SELECT * FROM dance_applications WHERE id = ?');
$stmt->execute([$id]);
$application = $stmt->fetch();
if (!$application) {
    http_response_code(404);
    exit('找不到這筆報名資料。');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $status = trim((string) ($_POST['status'] ?? ''));
    $adminNotes = mb_substr(trim((string) ($_POST['admin_notes'] ?? '')), 0, 5000, 'UTF-8');
    if (!isset($statusOptions[$status])) {
        $error = '請選擇有效的處理狀態。';
    } else {
        $update = db()->prepare('UPDATE dance_applications SET status = ?, admin_notes = ? WHERE id = ?');
        $update->execute([$status, $adminNotes !== '' ? $adminNotes : null, $id]);
        audit('update', 'dance_applications', $id, ['status' => $status, 'admin_notes' => $adminNotes]);
        flash('success', '報名資料的處理狀態已更新。');
        redirect('dance-application.php?id=' . $id);
    }
}

render_header('處理熱舞社報名 #' . $id, 'dance-applications');
?>
<?php if ($error): ?><div class="notice notice--error"><?= h($error) ?></div><?php endif; ?>
<div class="toolbar"><a class="button" href="dance-applications.php"><i class="bi bi-arrow-left"></i>回報名清單</a></div>

<section class="panel application-detail">
    <div class="panel__head"><div><h2><?= h($application['nickname']) ?></h2><p>送出於 <?= h($application['created_at']) ?></p></div><span class="status status--<?= h($application['status']) ?>"><?= h($statusOptions[$application['status']] ?? $application['status']) ?></span></div>
    <div class="detail-grid">
        <div class="detail-item"><span>暱稱</span><strong><?= h($application['nickname']) ?></strong></div>
        <div class="detail-item"><span>Email</span><a href="mailto:<?= h($application['email']) ?>"><?= h($application['email']) ?></a></div>
        <div class="detail-item"><span>練舞經驗</span><strong><?= $application['dance_years'] !== null ? (int) $application['dance_years'] . ' 年' : '未填寫' ?></strong></div>
        <div class="detail-item"><span>可參與日期</span><strong><?= h($application['available_date'] ?: '未填寫') ?></strong></div>
        <div class="detail-item"><span>20TH 經驗</span><strong><?= h(dance_attendance_label($application['attended_20th'])) ?></strong></div>
        <div class="detail-item"><span>想練的歌曲</span><strong><?= h(dance_song_label($application['song'])) ?></strong></div>
        <div class="detail-item detail-item--wide"><span>想參與的內容</span><strong><?= h(implode('、', dance_content_labels($application['participate_content']))) ?></strong></div>
        <div class="detail-item detail-item--wide"><span>30TH 留言</span><div class="detail-value"><?= $application['message_30th'] ? nl2br(h($application['message_30th'])) : '未填寫' ?></div></div>
        <div class="detail-item detail-item--wide"><span>參考檔案</span><?php if ($application['reference_file_name']): ?><a class="button detail-download" href="dance-application-file.php?id=<?= $id ?>"><i class="bi bi-download"></i><?= h($application['reference_original_name'] ?: '下載檔案') ?><?php if ($application['reference_file_size']): ?>（<?= h(number_format((int) $application['reference_file_size'] / 1024 / 1024, 2)) ?> MB）<?php endif; ?></a><?php else: ?><strong>未上傳</strong><?php endif; ?></div>
    </div>
</section>

<form class="panel editor" method="post">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="form-grid">
        <label class="field"><span>處理狀態 *</span><select name="status" required><?php foreach ($statusOptions as $value => $label): ?><option value="<?= h($value) ?>" <?= $application['status'] === $value ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></label>
        <label class="field field--wide"><span>內部備註</span><textarea name="admin_notes" rows="6" maxlength="5000" placeholder="例如：已寄信、等待確認練習日期……"><?= h((string) $application['admin_notes']) ?></textarea></label>
    </div>
    <div class="form-actions"><a class="button" href="dance-applications.php">取消</a><button class="button button--primary" type="submit">儲存處理狀態</button></div>
</form>
