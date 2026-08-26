<?php
declare(strict_types=1);

function fr_h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$fans = [];
$dbError = false;
try {
    $config = require __DIR__ . '/../admin/config.php';
    $db = $config['db'];
    $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $fans = $pdo->query(
        "SELECT nickname, join_time, fan_items, story
         FROM fan_roster
         WHERE status = 'published'
         ORDER BY sort_order, id"
    )->fetchAll();
} catch (Throwable $e) {
    $dbError = true;
}
?>
<!doctype html>
<html lang="zh-TW">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>sechskies_fans｜小黃集點卡</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>

<body>

  <!-- 共用導覽列 -->
  <nav class="navbar navbar-expand-lg navbar-dark sk-navbar sticky-top">
    <div class="container">
      <a class="navbar-brand" href="../practice-room.html"><i class="bi bi-gem me-1"></i>小黃同樂會 －YELLOWKIES FESTIVAL</a>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-brand btn-sm" href="../practice-room.html">水晶練習室</a>
        <a class="btn btn-brand btn-sm" href="../admin/records.php?module=fan_roster">後台管理</a>
      </div>
    </div>
  </nav>

  <main class="container py-5">

    <div class="text-center mb-5">
      <span class="eyebrow mb-3">小黃同樂會・SECHSKIES FAN ROSTER</span>
      <h1 class="display-5 fw-bold text-white mt-3 mb-0">小黃集點卡</h1>
      <span class="sub-title">應援物與應援事蹟紀錄</span>
      <p class="lead text-body-secondary mt-4 mx-auto" style="max-width: 640px;">
        整理歷屆台灣小黃留下的應援物與應援事蹟，讓新加入的 Yellowkies 也能認識這些累積下來的故事。
      </p>
    </div>

    <div class="row g-4" id="fan-list">
      <?php if ($dbError): ?>
      <div class="col-12">
        <div class="card sk-card p-4 text-center text-body-secondary">資料庫尚未連線，暫時無法載入粉絲名單。</div>
      </div>
      <?php elseif (!$fans): ?>
      <div class="col-12">
        <div class="card sk-card p-4 text-center text-body-secondary">目前還沒有公開的粉絲資料。</div>
      </div>
      <?php else: foreach ($fans as $fan): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card sk-card h-100 p-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="sk-welcome-icon" style="width:2.5rem;height:2.5rem;font-size:1.1rem;"><i
                  class="bi bi-person-heart"></i></div>
              <span class="fs-5 fw-bold text-white"><?= fr_h($fan['nickname']) ?></span>
            </div>
            <span class="badge badge-status badge-status--done">公開</span>
          </div>
          <p class="text-body-secondary small mb-2"><i class="bi bi-calendar-heart me-1"></i>入坑時間：<?= fr_h($fan['join_time']) ?></p>
          <?php $items = array_values(array_filter(array_map('trim', explode(',', (string) $fan['fan_items'])))); ?>
          <?php if ($items): ?>
          <div class="d-flex flex-wrap gap-2 mb-3">
            <?php foreach ($items as $item): ?>
            <span class="chip small"><i class="bi bi-gem" aria-hidden="true"></i> <?= fr_h($item) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <p class="text-body-secondary small mb-0"><?= fr_h($fan['story']) ?></p>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>

  </main>

  <footer>
    <p>SECHSKIES Fanpage &copy; 2026 - 黃色氣球永遠飄揚</p>
    <a href="https://jekkinoopy.github.io/Portfolio/" class="studio-link" target="_blank" rel="noopener noreferrer">
      Design by <span class="mark_b">Jekkinoopy Studio</span>
    </a>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>