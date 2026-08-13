<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
if (!empty($_SESSION['admin_id'])) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $stmt = db()->prepare('SELECT * FROM admin_users WHERE username = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([trim((string) ($_POST['username'] ?? ''))]);
        $user = $stmt->fetch();
        if ($user && password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $user['id'];
            $_SESSION['admin_name'] = $user['display_name'];
            db()->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
            audit('login', 'admin_users', (int) $user['id']);
            redirect('index.php');
        }
        $error = '帳號或密碼輸入錯誤。';
    } catch (Throwable $e) {
        $error = '資料庫尚未完成設定，請先執行初始化。';
    }
}
?>
<!doctype html><html lang="zh-Hant"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>管理登入｜sechskies_fans</title><link rel="stylesheet" href="assets/admin.css"></head>
<body class="auth-page"><main class="auth-card"><a class="auth-brand" href="../jekki-dance.html" aria-label="返回 sechskies_fans 前台"><span>sechskies_fans</span><span>CONTENT MANAGEMENT SYSTEM</span></a><p class="eyebrow">SECHSKIES DANCE ARCHIVE</p><h1>水晶熱舞社管理登入</h1><p>練舞清單與 30TH 應援報名，獨立管理。</p><?php if (isset($_GET['setup'])): ?><div class="notice notice--success">管理者建立完成，請登入。</div><?php endif; ?><?php if ($error): ?><div class="notice notice--error"><?= h($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><label>管理者帳號<input name="username" required autocomplete="username" autofocus></label><label>密碼<input name="password" type="password" required autocomplete="current-password"></label><button class="button button--primary" type="submit">登入後臺</button><a class="text-link" href="setup.php">第一次使用？初始化系統</a></form></main></body></html>
