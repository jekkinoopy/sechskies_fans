<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? 'create');
    if ($action === 'create') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $name = trim((string) ($_POST['display_name'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_.-]{4,50}$/', $username) || mb_strlen($name) < 2 || strlen($password) < 10) {
            $error = '請確認帳號、名稱與至少 10 字元的密碼。';
        } else {
            try {
                $stmt = db()->prepare('INSERT INTO admin_users (username, password_hash, display_name, email) VALUES (?, ?, ?, ?)');
                $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $name, trim((string) ($_POST['email'] ?? '')) ?: null]);
                audit('create', 'admin_users', (int) db()->lastInsertId());
                flash('success', '管理者已新增。'); redirect('admins.php');
            } catch (PDOException $e) { $error = '帳號已存在或資料格式不正確。'; }
        }
    } elseif ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) $_SESSION['admin_id']) $error = '不能停用目前登入的帳號。';
        else { db()->prepare('UPDATE admin_users SET is_active = 1 - is_active WHERE id = ?')->execute([$id]); audit('toggle', 'admin_users', $id); flash('success', '帳號狀態已更新。'); redirect('admins.php'); }
    }
}
$users = db()->query('SELECT id, username, display_name, email, is_active, last_login_at, created_at FROM admin_users ORDER BY id')->fetchAll();
render_header('管理者帳號', 'admins');
?>
<?php if ($error): ?><div class="notice notice--error"><?= h($error) ?></div><?php endif; ?>
<section class="panel"><div class="panel__head"><div><h2>新增管理者</h2><p>密碼使用不可逆雜湊儲存</p></div></div><form class="inline-form" method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="create"><label>登入帳號<input name="username" required></label><label>顯示名稱<input name="display_name" required></label><label>電子信箱<input name="email" type="email"></label><label>密碼<input name="password" type="password" minlength="10" required></label><button class="button button--primary" type="submit">新增帳號</button></form></section>
<section class="panel"><div class="panel__head"><div><h2>帳號清單</h2><p>停用帳號後將無法登入</p></div></div><div class="table-wrap"><table><thead><tr><th>帳號</th><th>顯示名稱</th><th>信箱</th><th>最後登入</th><th>狀態</th><th>操作</th></tr></thead><tbody><?php foreach ($users as $user): ?><tr><td><?= h($user['username']) ?></td><td><?= h($user['display_name']) ?></td><td><?= h($user['email']) ?></td><td><?= h($user['last_login_at'] ?? '尚未登入') ?></td><td><span class="status status--<?= $user['is_active'] ? 'published' : 'archived' ?>"><?= $user['is_active'] ? '啟用' : '停用' ?></span></td><td><form method="post" data-confirm="確定變更此管理者狀態？"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $user['id'] ?>"><button class="table-button" type="submit" <?= (int) $user['id'] === (int) $_SESSION['admin_id'] ? 'disabled' : '' ?>><?= $user['is_active'] ? '停用' : '啟用' ?></button></form></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php render_footer();
