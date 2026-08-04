<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;
$query = trim((string) ($_GET['q'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$statusOptions = dance_application_statuses();
$where = [];
$params = [];

if ($query !== '') {
    $where[] = '(nickname LIKE ? OR email LIKE ? OR message_30th LIKE ?)';
    $searchValue = '%' . $query . '%';
    $params = [$searchValue, $searchValue, $searchValue];
}
if ($status !== '' && isset($statusOptions[$status])) {
    $where[] = 'status = ?';
    $params[] = $status;
} else {
    $status = '';
}

$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$countStmt = db()->prepare('SELECT COUNT(*) FROM dance_applications' . $whereSql);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$stmt = db()->prepare('SELECT * FROM dance_applications' . $whereSql . " ORDER BY created_at DESC, id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$rows = $stmt->fetchAll();

render_header('30TH 應援報名', 'dance-applications');
?>
<div class="toolbar">
    <form class="filters" method="get">
        <label class="search"><i class="bi bi-search"></i><input name="q" value="<?= h($query) ?>" placeholder="搜尋暱稱、Email 或留言"></label>
        <select name="status" aria-label="處理狀態">
            <option value="">全部狀態</option>
            <?php foreach ($statusOptions as $value => $label): ?>
                <option value="<?= h($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= h($label) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="button" type="submit">篩選</button>
    </form>
    <a class="button" href="../crystal-dance-survey.html#form-30th" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i>查看募集表單</a>
</div>

<section class="panel">
    <div class="panel__head"><div><h2>報名清單</h2><p>共 <?= $total ?> 筆；Email 與上傳檔案僅供企劃聯絡使用</p></div></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>編號</th><th>暱稱</th><th>Email</th><th>想參與</th><th>歌曲</th><th>狀態</th><th>送出時間</th><th>操作</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>#<?= (int) $row['id'] ?></td>
                    <td><?= h($row['nickname']) ?></td>
                    <td><?= h(mb_strimwidth((string) $row['email'], 0, 34, '…', 'UTF-8')) ?></td>
                    <td><?= h(implode('、', dance_content_labels($row['participate_content']))) ?></td>
                    <td><?= h(dance_song_label($row['song'])) ?></td>
                    <td><span class="status status--<?= h($row['status']) ?>"><?= h($statusOptions[$row['status']] ?? $row['status']) ?></span></td>
                    <td><?= h($row['created_at']) ?></td>
                    <td class="actions"><a href="dance-application.php?id=<?= (int) $row['id'] ?>">查看／處理</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="8" class="empty">目前沒有符合條件的報名資料。</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
        <nav class="pagination" aria-label="報名資料分頁">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a class="<?= $i === $page ? 'active' : '' ?>" href="?q=<?= urlencode($query) ?>&status=<?= urlencode($status) ?>&page=<?= $i ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>
<?php render_footer();
