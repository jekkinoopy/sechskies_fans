<?php
declare(strict_types=1);

session_start();
$config = require __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo;
    global $config;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $db = $config['db'];
    $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $token = (string) ($_POST['csrf'] ?? '');
    if (!hash_equals((string) ($_SESSION['csrf'] ?? ''), $token)) {
        http_response_code(419);
        exit('表單已過期，請返回後重新操作。');
    }
}

function require_login(): void
{
    if (empty($_SESSION['admin_id'])) {
        redirect('login.php');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function modules(): array
{
    return [
        'dance_practices' => [
            'label' => '練舞清單', 'item_label' => '練舞項目', 'list_title' => '練舞清單', 'icon' => 'list-check', 'table' => 'dance_practice_items', 'order' => 'sort_order, id',
            'fields' => [
                'title' => ['label' => '歌曲名稱', 'type' => 'text', 'required' => true],
                'tags' => ['label' => '練習分類', 'type' => 'text'],
                'practice_focus' => ['label' => '練習重點', 'type' => 'textarea', 'required' => true],
                'video_url' => ['label' => '參考影片網址', 'type' => 'url'],
                'difficulty' => ['label' => '難度', 'type' => 'select', 'default' => 'medium', 'required' => true, 'options' => ['easy' => '入門', 'medium' => '中等', 'hard' => '挑戰']],
                'progress_status' => ['label' => '練習進度', 'type' => 'select', 'default' => 'not_started', 'required' => true, 'options' => ['not_started' => '未開始', 'practicing' => '練習中', 'review' => '待複習', 'completed' => '已完成']],
                'note_text' => ['label' => '補充說明', 'type' => 'text'],
                'is_featured' => ['label' => '優先練習', 'type' => 'checkbox'],
                'sort_order' => ['label' => '排序', 'type' => 'number', 'default' => '0'],
                'status' => ['label' => '公開狀態', 'type' => 'status', 'required' => true],
            ],
            'list' => ['title', 'difficulty', 'progress_status', 'is_featured', 'sort_order', 'status', 'updated_at'],
        ],
    ];
}

function module_config(string $key): array
{
    $all = modules();
    if (!isset($all[$key])) {
        http_response_code(404);
        exit('找不到管理模組。');
    }
    return $all[$key];
}

function status_label(string $status): string
{
    return ['draft' => '草稿', 'coming_soon' => '籌備中', 'published' => '公開', 'archived' => '封存', 'available' => '可使用'][$status] ?? $status;
}

function dance_application_statuses(): array
{
    return [
        'new' => '新報名',
        'contacted' => '已聯絡',
        'confirmed' => '確認參與',
        'declined' => '暫不參與',
        'archived' => '已封存',
    ];
}

function dance_attendance_label(?string $value): string
{
    return [
        'attended' => '參加過 20TH',
        'watched_video' => '看過 20TH 影片',
        'first_time' => '第一次知道',
    ][$value ?? ''] ?? '未填寫';
}

function dance_song_label(?string $value): string
{
    return [
        'couple' => 'Couple',
        'comeback' => "Com' Back",
        'road_fighter' => 'Road Fighter',
        'pom_saeng_pom_sa' => 'Pom Saeng Pom Sa',
        'other' => '其他',
    ][$value ?? ''] ?? '未選擇';
}

function dance_content_labels(?string $value): array
{
    $labels = [
        'cover' => '翻跳',
        'medley' => '串燒聯舞',
        'blessing' => '祝福影片',
        'graphic' => '圖文應援',
        'editing' => '影片剪輯',
        'archive' => '資料整理',
    ];
    $selected = array_filter(array_map('trim', explode(',', (string) $value)));
    return array_map(static fn (string $item): string => $labels[$item] ?? $item, $selected);
}

function audit(string $action, string $entityType, ?int $entityId, array $details = []): void
{
    $stmt = db()->prepare('INSERT INTO audit_logs (admin_user_id, action_name, entity_type, entity_id, details_json, ip_address) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$_SESSION['admin_id'] ?? null, $action, $entityType, $entityId, json_encode($details, JSON_UNESCAPED_UNICODE), $_SERVER['REMOTE_ADDR'] ?? null]);
}

function render_header(string $title, string $active = ''): void
{
    global $config;
    $flash = take_flash();
    $mods = modules();
    ?>
<!doctype html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?>｜<?= h($config['site_name']) ?> Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<header class="admin-topbar">
    <a class="admin-brand" href="index.php" aria-label="sechskies_fans 後台首頁">
        <span>sechskies_fans</span>
        <small>Admin</small>
    </a>
    <div class="admin-topbar__actions"><span><?= h($_SESSION['admin_name'] ?? '') ?></span><a href="../jekki-dance.html" target="_blank" rel="noopener">查看前臺</a><a href="logout.php">登出</a></div>
</header>
<div class="admin-shell">
    <aside class="admin-sidebar" id="admin-sidebar">
        <nav aria-label="後臺管理選單">
            <a class="<?= $active === 'dashboard' ? 'active' : '' ?>" href="index.php"><i class="bi bi-speedometer2"></i>控制台</a>
            <p>水晶熱舞社</p>
            <?php foreach ($mods as $key => $mod): ?>
                <a class="<?= $active === $key ? 'active' : '' ?>" href="records.php?module=<?= h($key) ?>"><i class="bi bi-<?= h($mod['icon']) ?>"></i><?= h($mod['label']) ?></a>
            <?php endforeach; ?>
            <a class="<?= $active === 'dance-applications' ? 'active' : '' ?>" href="dance-applications.php"><i class="bi bi-person-hearts"></i>30TH 應援報名</a>
            <p>系統</p>
            <a class="<?= $active === 'publishing' ? 'active' : '' ?>" href="publishing.php"><i class="bi bi-eye"></i>公開狀態</a>
            <a class="<?= $active === 'admins' ? 'active' : '' ?>" href="admins.php"><i class="bi bi-shield-lock"></i>管理者帳號</a>
        </nav>
    </aside>
    <main class="admin-main">
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="開啟管理選單"><i class="bi bi-list"></i></button>
        <?php if ($flash): ?><div class="notice notice--<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div><?php endif; ?>
        <div class="page-heading"><div><p class="eyebrow">CONTENT MANAGEMENT</p><h1><?= h($title) ?></h1></div></div>
    <?php
}

function render_footer(): void
{
    ?>
    </main>
</div>
<script src="assets/admin.js"></script>
</body>
</html>
    <?php
}
