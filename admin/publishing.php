<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();
$rows = [];
foreach (modules() as $key => $module) {
    $counts = ['draft' => 0, 'coming_soon' => 0, 'published' => 0, 'archived' => 0];
    foreach (db()->query("SELECT status, COUNT(*) total FROM `{$module['table']}` GROUP BY status")->fetchAll() as $row) $counts[$row['status']] = (int) $row['total'];
    $rows[] = ['key' => $key, 'label' => $module['label'], 'counts' => $counts];
}
render_header('公開狀態', 'publishing');
?>
<section class="panel"><div class="panel__head"><div><h2>各模組發布概況</h2><p>公開前先完成資料與來源檢查</p></div></div><div class="table-wrap"><table><thead><tr><th>內容模組</th><th>草稿</th><th>籌備中</th><th>公開</th><th>封存</th><th>管理</th></tr></thead><tbody><?php foreach ($rows as $row): ?><tr><td><?= h($row['label']) ?></td><td><?= $row['counts']['draft'] ?></td><td><?= $row['counts']['coming_soon'] ?></td><td><?= $row['counts']['published'] ?></td><td><?= $row['counts']['archived'] ?></td><td><a href="records.php?module=<?= h($row['key']) ?>">查看資料</a></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php render_footer();
