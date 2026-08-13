<?php
declare(strict_types=1);

$config = require __DIR__ . '/admin/config.php';

function return_to_dance_form(string $result): never
{
    header('Location: jekki-dance.html?form=' . rawurlencode($result) . '#form-30th', true, 303);
    exit;
}

function post_value(string $key, int $maxLength): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    return mb_substr($value, 0, $maxLength, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

$maxUploadBytes = (int) $config['dance_max_upload_bytes'];
$contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($contentLength > $maxUploadBytes + 1024 * 1024) {
    return_to_dance_form('file_too_large');
}

// 隱藏欄位有內容時視為機器送件；不透露攔截規則。
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    return_to_dance_form('success');
}

$nickname = post_value('nickname', 80);
$email = mb_strtolower(post_value('email', 190), 'UTF-8');
$danceYearsRaw = trim((string) ($_POST['dance_years'] ?? ''));
$availableDate = trim((string) ($_POST['available_date'] ?? ''));
$attended20th = trim((string) ($_POST['attended_20th'] ?? ''));
$song = trim((string) ($_POST['song'] ?? ''));
$message30th = post_value('message_30th', 5000);

$allowedAttendance = ['attended', 'watched_video', 'first_time'];
$allowedContent = ['cover', 'medley', 'blessing', 'graphic', 'editing', 'archive'];
$allowedSongs = ['', 'couple', 'comeback', 'road_fighter', 'pom_saeng_pom_sa', 'other'];

$selectedContent = $_POST['participate_content'] ?? [];
if (!is_array($selectedContent)) {
    $selectedContent = [$selectedContent];
}
$selectedContent = array_values(array_unique(array_intersect(
    $allowedContent,
    array_map(static fn ($value): string => trim((string) $value), $selectedContent)
)));

if ($nickname === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$selectedContent) {
    return_to_dance_form('invalid');
}
if ($attended20th !== '' && !in_array($attended20th, $allowedAttendance, true)) {
    return_to_dance_form('invalid');
}
if (!in_array($song, $allowedSongs, true)) {
    return_to_dance_form('invalid');
}

$danceYears = null;
if ($danceYearsRaw !== '') {
    $danceYearsFilter = filter_var($danceYearsRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 30]]);
    if ($danceYearsFilter === false) {
        return_to_dance_form('invalid');
    }
    $danceYears = (int) $danceYearsFilter;
}

if ($availableDate !== '') {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $availableDate);
    if (!$date || $date->format('Y-m-d') !== $availableDate) {
        return_to_dance_form('invalid');
    }
}

$db = $config['db'];
$dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException) {
    return_to_dance_form('unavailable');
}

$submittedIp = mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45, 'UTF-8');
if ($submittedIp !== '') {
    $rate = $pdo->prepare('SELECT COUNT(*) FROM dance_applications WHERE submitted_ip = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)');
    $rate->execute([$submittedIp]);
    if ((int) $rate->fetchColumn() >= 3) {
        return_to_dance_form('too_many');
    }
}

$storedFileName = null;
$originalFileName = null;
$mimeType = null;
$fileSize = null;
$file = $_FILES['reference_file'] ?? null;
if (is_array($file) && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    if ((int) $file['error'] !== UPLOAD_ERR_OK || (int) $file['size'] > $maxUploadBytes) {
        return_to_dance_form('file_too_large');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/quicktime' => 'mov',
    ];
    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']) ?: '';
    if (!isset($allowedMimeTypes[$mimeType])) {
        return_to_dance_form('file_type');
    }

    $uploadDir = (string) $config['dance_upload_dir'];
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0750, true) && !is_dir($uploadDir)) {
        return_to_dance_form('unavailable');
    }
    $storedFileName = bin2hex(random_bytes(20)) . '.' . $allowedMimeTypes[$mimeType];
    if (!move_uploaded_file((string) $file['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $storedFileName)) {
        return_to_dance_form('unavailable');
    }
    $originalFileName = mb_substr(basename((string) $file['name']), 0, 255, 'UTF-8');
    $fileSize = (int) $file['size'];
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO dance_applications
        (nickname, email, dance_years, available_date, attended_20th, participate_content, song,
         reference_file_name, reference_original_name, reference_mime_type, reference_file_size,
         message_30th, submitted_ip, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $nickname,
        $email,
        $danceYears,
        $availableDate !== '' ? $availableDate : null,
        $attended20th !== '' ? $attended20th : null,
        implode(',', $selectedContent),
        $song !== '' ? $song : null,
        $storedFileName,
        $originalFileName,
        $mimeType,
        $fileSize,
        $message30th !== '' ? $message30th : null,
        $submittedIp !== '' ? $submittedIp : null,
        mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255, 'UTF-8'),
    ]);
} catch (PDOException) {
    if ($storedFileName !== null) {
        @unlink((string) $config['dance_upload_dir'] . DIRECTORY_SEPARATOR . $storedFileName);
    }
    return_to_dance_form('unavailable');
}

return_to_dance_form('success');
