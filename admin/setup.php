<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

$error = '';
try {
    $count = (int) db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    if ($count > 0) {
        redirect('login.php');
    }
} catch (Throwable $e) {
    $error = '尚未建立資料表。請先在 phpMyAdmin 匯入 admin/database/schema.sql。';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $displayName = trim((string) ($_POST['display_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_.-]{4,50}$/', $username)) {
        $error = '帳號須為 4–50 個英數字或 ._-。';
    } elseif (mb_strlen($displayName) < 2) {
        $error = '請填寫管理者顯示名稱。';
    } elseif (strlen($password) < 10) {
        $error = '密碼至少需要 10 個字元。';
    } else {
        $stmt = db()->prepare('INSERT INTO admin_users (username, password_hash, display_name, email) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $displayName, $email ?: null]);
        redirect('login.php?setup=1');
    }
}
?>
<!doctype html><html lang="zh-Hant"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>初始化後臺</title><link rel="stylesheet" href="assets/admin.css"></head>
<body class="auth-page"><main class="auth-card"><a class="auth-brand" href="../practice-room.html" aria-label="返回 sechskies_fans 前台"><span>sechskies_fans</span><span>CONTENT MANAGEMENT SYSTEM</span></a><p class="eyebrow">FIRST-TIME SETUP</p><h1>建立第一位管理者</h1><p>sechskies_fans 內容管理系統</p><?php if ($error): ?><div class="notice notice--error"><?= h($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><label>登入帳號<input name="username" required autocomplete="username"></label><label>顯示名稱<input name="display_name" required></label><label>電子信箱<input name="email" type="email"></label><label>登入密碼<input name="password" type="password" required minlength="10" autocomplete="new-password"></label><button class="button button--primary" type="submit">建立管理者</button></form></main></body></html>
