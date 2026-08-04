<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();

$key = (string) ($_GET['module'] ?? '');
$module = module_config($key);
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;
$query = trim((string) ($_GET['q'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$where = [];
$params = [];
if ($query !== '') {
    $searchable = [];
    foreach ($module['fields'] as $name => $field) {
        if (in_array($field['type'], ['text', 'textarea', 'url'], true)) $searchable[] = "`$name` LIKE ?";
    }
    if ($searchable) {
        $where[] = '(' . implode(' OR ', $searchable) . ')';
        foreach ($searchable as $_) $params[] = '%' . $query . '%';
    }
}
if ($status !== '' && isset($module['fields']['status'])) {
    $where[] = '`status` = ?';
    $params[] = $status;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$countStmt = db()->prepare('SELECT COUNT(*) FROM `' . $module['table'] . '`' . $whereSql);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;
$stmt = db()->prepare('SELECT * FROM `' . $module['table'] . '`' . $whereSql . ' ORDER BY ' . $module['order'] . " LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$rows = $stmt->fetchAll();

$relations = [];
foreach ($module['fields'] as $name => $field) {
    if ($field['type'] === 'relation') {
        $relations[$name] = [];
        foreach (db()->query("SELECT id, title FROM `{$field['table']}`")->fetchAll() as $rel) $relations[$name][$rel['id']] = $rel['title'];
    }
}

render_header($module['label'], $key);
?>
<div class="toolbar"><form class="filters" method="get"><input type="hidden" name="module" value="<?= h($key) ?>"><label class="search"><i class="bi bi-search"></i><input name="q" value="<?= h($query) ?>" placeholder="搜尋本區資料"></label><?php if (isset($module['fields']['status'])): ?><select name="status"><option value="">全部狀態</option><?php foreach (['draft' => '草稿', 'coming_soon' => '籌備中', 'published' => '公開', 'archived' => '封存', 'available' => '可使用'] as $value => $label): ?><option value="<?= $value ?>" <?= $status === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select><?php endif; ?><button class="button" type="submit">篩選</button></form><a class="button button--primary" href="record-form.php?module=<?= h($key) ?>"><i class="bi bi-plus-lg"></i>新增資料</a></div>
<section class="panel"><div class="panel__head"><div><h2><?= h($module['list_title'] ?? ($module['label'] . '清單')) ?></h2><p>共 <?= $total ?> 筆；每頁 <?= $perPage ?> 筆</p></div></div><div class="table-wrap"><table><thead><tr><th>編號</th><?php foreach ($module['list'] as $field): ?><th><?= h($module['fields'][$field]['label'] ?? $field) ?></th><?php endforeach; ?><th>操作</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td>#<?= (int) $row['id'] ?></td><?php foreach ($module['list'] as $field): ?><td><?php $value = $row[$field] ?? ''; if ($field === 'status'): ?><span class="status status--<?= h($value) ?>"><?= h(status_label($value)) ?></span><?php elseif (isset($relations[$field])): ?><?= h($relations[$field][$value] ?? ('#' . $value)) ?><?php elseif (($module['fields'][$field]['type'] ?? '') === 'checkbox'): ?><?= $value ? '是' : '否' ?><?php elseif (($module['fields'][$field]['type'] ?? '') === 'select'): ?><?= h($module['fields'][$field]['options'][$value] ?? (string) $value) ?><?php else: ?><?= h(mb_strimwidth((string) $value, 0, 42, '…', 'UTF-8')) ?><?php endif; ?></td><?php endforeach; ?><td class="actions"><a href="record-form.php?module=<?= h($key) ?>&id=<?= (int) $row['id'] ?>">編輯</a><form method="post" action="record-delete.php" data-confirm="確定刪除這筆資料？此動作無法復原。"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="module" value="<?= h($key) ?>"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><button type="submit">刪除</button></form></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="<?= count($module['list']) + 2 ?>" class="empty">找不到符合條件的資料。</td></tr><?php endif; ?></tbody></table></div>
<?php if ($pages > 1): ?><nav class="pagination" aria-label="資料分頁"><?php for ($i = 1; $i <= $pages; $i++): ?><a class="<?= $i === $page ? 'active' : '' ?>" href="?module=<?= h($key) ?>&q=<?= urlencode($query) ?>&status=<?= urlencode($status) ?>&page=<?= $i ?>"><?= $i ?></a><?php endfor; ?></nav><?php endif; ?></section>
<?php render_footer();
