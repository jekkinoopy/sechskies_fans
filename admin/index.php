<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();

$stats = [];
foreach (modules() as $key => $module) {
    $stats[$key] = (int) db()->query('SELECT COUNT(*) FROM `' . $module['table'] . '`')->fetchColumn();
}
$published = 0;
$comingSoon = 0;
foreach (modules() as $key => $module) {
    if ($key === 'media') continue;
    $published += (int) db()->query("SELECT COUNT(*) FROM `{$module['table']}` WHERE status = 'published'")->fetchColumn();
    $comingSoon += (int) db()->query("SELECT COUNT(*) FROM `{$module['table']}` WHERE status = 'coming_soon'")->fetchColumn();
}
$recent = db()->query('SELECT a.*, u.display_name FROM audit_logs a LEFT JOIN admin_users u ON u.id = a.admin_user_id ORDER BY a.id DESC LIMIT 8')->fetchAll();
render_header('控制台', 'dashboard');
?>
<section class="stat-grid">
    <article class="stat-card"><span>已公開內容</span><strong><?= $published ?></strong><i class="bi bi-broadcast"></i></article>
    <article class="stat-card"><span>籌備中內容</span><strong><?= $comingSoon ?></strong><i class="bi bi-hourglass-split"></i></article>
    <article class="stat-card"><span>媒體素材</span><strong><?= $stats['media'] ?></strong><i class="bi bi-images"></i></article>
    <article class="stat-card"><span>全部內容</span><strong><?= array_sum($stats) ?></strong><i class="bi bi-archive"></i></article>
</section>
<section class="panel"><div class="panel__head"><div><h2>內容概況</h2><p>依本站真正的資料分類統計</p></div></div><div class="module-grid">
<?php foreach (modules() as $key => $module): ?><a href="records.php?module=<?= h($key) ?>"><i class="bi bi-<?= h($module['icon']) ?>"></i><span><?= h($module['label']) ?></span><strong><?= $stats[$key] ?></strong></a><?php endforeach; ?>
</div></section>
<section class="panel"><div class="panel__head"><div><h2>最近管理紀錄</h2><p>保留新增、修改、刪除與登入軌跡</p></div></div>
<div class="table-wrap"><table><thead><tr><th>時間</th><th>管理者</th><th>動作</th><th>資料類型</th><th>編號</th></tr></thead><tbody>
<?php foreach ($recent as $row): ?><tr><td><?= h($row['created_at']) ?></td><td><?= h($row['display_name'] ?? '系統') ?></td><td><?= h($row['action_name']) ?></td><td><?= h($row['entity_type']) ?></td><td><?= h((string) $row['entity_id']) ?></td></tr><?php endforeach; ?>
<?php if (!$recent): ?><tr><td colspan="5" class="empty">目前尚無管理紀錄。</td></tr><?php endif; ?>
</tbody></table></div></section>
<?php render_footer();
