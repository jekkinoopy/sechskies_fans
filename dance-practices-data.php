<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$config = require __DIR__ . '/admin/config.php';
$db = $config['db'];
$dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $rows = $pdo->query(
        "SELECT id, title, tags, practice_focus, video_url, difficulty, progress_status,
                note_text, is_featured, sort_order, status
         FROM dance_practice_items
         WHERE status IN ('published', 'coming_soon')
         ORDER BY sort_order, id"
    )->fetchAll();
} catch (PDOException) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'items' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

$difficultyLabels = ['easy' => '入門', 'medium' => '中等', 'hard' => '挑戰'];
$progressLabels = ['not_started' => '未開始', 'practicing' => '練習中', 'review' => '待複習', 'completed' => '已完成'];

$items = array_map(static function (array $row) use ($difficultyLabels, $progressLabels): array {
    $videoUrl = trim((string) ($row['video_url'] ?? ''));
    if ($videoUrl !== '' && (!filter_var($videoUrl, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $videoUrl))) {
        $videoUrl = '';
    }
    return [
        'id' => (int) $row['id'],
        'title' => (string) $row['title'],
        'tags' => array_values(array_filter(array_map('trim', explode(',', (string) ($row['tags'] ?? ''))))),
        'practiceFocus' => (string) $row['practice_focus'],
        'videoUrl' => $videoUrl,
        'difficulty' => $difficultyLabels[$row['difficulty']] ?? (string) $row['difficulty'],
        'progress' => $progressLabels[$row['progress_status']] ?? (string) $row['progress_status'],
        'note' => (string) ($row['note_text'] ?? ''),
        'featured' => (bool) $row['is_featured'],
        'comingSoon' => $row['status'] === 'coming_soon',
    ];
}, $rows);

echo json_encode(['ok' => true, 'items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
