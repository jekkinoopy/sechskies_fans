<?php
/**
 * 水晶熱舞社 — 練舞場備天氣看板
 * 資料來源：中央氣象署開放資料平臺 F-D0047-069（新北市鄉鎮天氣預報）
 * 只服務板橋、新莊、泰山這幾個熱舞社常用練舞地點，直接選地點，不用自己對應行政區
 * 只顯示真的會練舞的時段：平日晚上 17-22 點、假日白天 11-22 點
 */

// ── 1. 授權碼：到 opendata.cwa.gov.tw 註冊會員後，於「取得授權碼」頁面複製，格式為 CWA-開頭
$apiKey = 'CWA-2D933FEF-1CA0-46C9-AAAF-4B0E6F36038C';

// ── 2. 練舞地點清單：練舞地點名稱 → 對應的氣象署行政區（分組僅用於下拉選單顯示）
$venueGroups = [
    '板橋' => [
        '板橋車站' => '板橋區',
    ],
    '新莊' => [
        '新莊國民運動中心' => '新莊區',
    ],
    '泰山' => [
        '泰山職業訓練場' => '泰山區',
        '明志科技大學'   => '泰山區',
    ],
];
$venues = [];
foreach ($venueGroups as $group) {
    foreach ($group as $name => $district) {
        $venues[$name] = $district;
    }
}

// ── 3. 接收使用者選擇的練舞地點，沒選或亂改網址參數就用第一個當預設
$venue = $_GET['venue'] ?? array_key_first($venues);
if (!array_key_exists($venue, $venues)) {
    $venue = array_key_first($venues);
}
$district = $venues[$venue];

// ── 4. 組出 API 網址（地名有中文，一定要 urlencode）
$url = 'https://opendata.cwa.gov.tw/api/v1/rest/datastore/F-D0047-069'
     . '?Authorization=' . urlencode($apiKey)
     . '&format=JSON'
     . '&locationName=' . urlencode($district);

// ── 5. 發出請求
$error    = '';
$forecast = [];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
// 本機 XAMPP 常因為缺 CA 憑證而連線失敗，這行是本機權宜做法，正式上線要拿掉
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false) {
    $error = '連線失敗：' . $curlErr;
} elseif ($httpCode !== 200) {
    $error = 'API 回傳狀態碼 ' . $httpCode . '，多半是授權碼錯了或已失效。';
} else {
    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = 'JSON 解析失敗：' . json_last_error_msg();
    } elseif (empty($data['records']['Locations'][0]['Location'][0]['WeatherElement'])) {
        $error = '查無 ' . $district . ' 的資料，氣象署那邊可能暫時查不到。';
    } else {
        // ── 6. 把巢狀的 WeatherElement 攤平成好用的格式
        // 原始結構：records → Locations[0] → Location[0] → WeatherElement[] → Time[] → ElementValue
        $elements = [];
        foreach ($data['records']['Locations'][0]['Location'][0]['WeatherElement'] as $el) {
            $elements[$el['ElementName']] = $el['Time'];
        }

        // 「溫度」「舒適度指數」是逐小時資料，抓某個時段內的所有筆數用
        $hourlyInRange = function (array $hourly, string $start, string $end, string $key): array {
            $startTs = strtotime($start);
            $endTs   = strtotime($end);
            $values  = [];
            foreach ($hourly as $t) {
                $ts = strtotime($t['DataTime']);
                if ($ts >= $startTs && $ts < $endTs) {
                    $values[] = $t['ElementValue'][0][$key];
                }
            }
            return $values;
        };

        // 「天氣現象」「3小時降雨機率」本身就是 3 小時一筆，這裡不看全部時段，
        // 只挑真的會練舞的時間：平日晚上 17-22 點、假日白天 11-22 點
        $blocks = $elements['天氣現象'] ?? [];

        foreach ($blocks as $b) {
            $start   = $b['StartTime'];
            $end     = $b['EndTime'];
            $startTs = strtotime($start);
            $endTs   = strtotime($end);

            $dow       = (int) date('N', $startTs); // 1=一 ... 7=日
            $isWeekend = $dow >= 6;
            $dateStr   = date('Y-m-d', $startTs);

            if ($isWeekend) {
                $winStart = strtotime($dateStr . ' 11:00:00');
                $winEnd   = strtotime($dateStr . ' 22:00:00');
            } else {
                $winStart = strtotime($dateStr . ' 17:00:00');
                $winEnd   = strtotime($dateStr . ' 22:00:00');
            }

            // 時段跟練習時間完全沒重疊就跳過
            if ($startTs >= $winEnd || $endTs <= $winStart) {
                continue;
            }

            $pop = 0;
            foreach (($elements['3小時降雨機率'] ?? []) as $p) {
                if ($p['StartTime'] === $start) {
                    $pop = (int) $p['ElementValue'][0]['ProbabilityOfPrecipitation'];
                    break;
                }
            }

            $temps = array_map('intval', $hourlyInRange($elements['溫度'] ?? [], $start, $end, 'Temperature'));
            $ciList = $hourlyInRange($elements['舒適度指數'] ?? [], $start, $end, 'ComfortIndexDescription');

            $forecast[] = [
                'start'     => $start,
                'end'       => $end,
                'wx'        => $b['ElementValue'][0]['Weather'],
                'pop'       => $pop,
                'minT'      => $temps ? min($temps) : 0,
                'maxT'      => $temps ? max($temps) : 0,
                'ci'        => $ciList[0] ?? '',
                'isWeekend' => $isWeekend,
            ];
        }
    }
}

/**
 * 依照預報數值產生場備建議（icon、標籤、文字）
 */
function gearAdvice(array $f): array
{
    $tips = [];

    if ($f['pop'] >= 70) {
        $tips[] = ['bi-cloud-rain-heavy-fill', '降雨機率高', '直接改室內場，戶外場不用排'];
    } elseif ($f['pop'] >= 30) {
        $tips[] = ['bi-cloud-drizzle-fill', '可能下雨', '先問好室內備案場地，音響加防水套'];
    }

    if ($f['maxT'] >= 32) {
        $tips[] = ['bi-thermometer-sun-fill', '高溫悶熱', '多帶水和電風扇，中間排休息時段'];
    }

    if ($f['minT'] <= 16) {
        $tips[] = ['bi-thermometer-snow', '低溫偏冷', '暖身時間拉長，提醒社員帶外套'];
    }

    if (!$tips) {
        $tips[] = ['bi-check-circle-fill', '天氣穩定', '照原訂場地走，正常練舞'];
    }

    return $tips;
}

/**
 * 2026-09-08 06:00:00 → 9/8 06 時
 */
function shortTime(string $t): string
{
    $ts = strtotime($t);
    return date('n/j H', $ts) . ' 時';
}
?>
<!doctype html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YELLOW WAVE｜水晶氣象局</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* 頁面專屬微調：共用版型沒覆蓋到的部分（時段卡片、天氣數值、場備建議、錯誤訊息） */
        .slot-card .card-header {
            background-color: transparent !important;
            border-bottom-color: var(--sk-border) !important;
        }

        .slot-wx {
            font-size: 1.35rem;
            font-weight: 700;
            color: #fff;
        }

        .slot-nums span {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .tip-row {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
        }

        .tip-row .bi {
            color: var(--sk-secondary);
            font-size: 1.1rem;
            margin-top: 0.15rem;
        }

        #error-alert {
            background-color: rgba(226, 16, 43, 0.14);
            border: 1px solid rgba(226, 16, 43, 0.4);
            color: #ffb4bb;
        }
    </style>
</head>

<body>

    <!-- 共用導覽列 -->
    <nav class="navbar navbar-expand-lg navbar-dark sk-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand" href="../jekki-dance.html"><i class="bi bi-gem me-1"></i>小黃同樂會 － YELLOW WAVE</a>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-brand btn-sm" href="../jekki-dance.html">水晶熱舞社</a>
                <a class="btn btn-outline-brand btn-sm" href="../practice-room.html">水晶練習室</a>
                <a class="btn btn-outline-brand btn-sm" href="../admin/login.php">後台管理</a>
            </div>
        </div>
    </nav>

    <canvas id="particles"></canvas>

    <main class="container py-5">

        <div class="text-center mb-4">
            <img src="bn.jpg" alt="水晶氣象局 banner" class="img-fluid rounded-4 shadow-sm">
        </div>

        <div class="text-center mb-5">
            <span class="eyebrow mb-3">小黃同樂會・SECHSKIES DANCE WEATHER</span>
            <h1 class="display-5 fw-bold text-white mt-3 mb-0">水晶氣象局</h1>
            <span class="sub-title">板橋・新莊・泰山 練舞地點天氣看板</span>
            <p class="lead text-body-secondary mt-4 mx-auto" style="max-width: 640px;">
                水晶熱舞社常用的練舞地點都幫你排好了：板橋車站、新莊國民運動中心、泰山職業訓練場、明志科技大學。選一個地點，水晶氣象局只挑真的會練舞的時段——平日晚上
                5-10 點、假日早上 11 點到晚上 10 點，直接告訴你這個時段要多準備什麼。
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                <span class="chip"><i class="bi bi-moon-stars-fill" aria-hidden="true"></i> 平日 17-22 點</span>
                <span class="chip"><i class="bi bi-sun-fill" aria-hidden="true"></i> 假日 11-22 點</span>
                <span class="badge badge-type badge-type--dance">熱舞社</span>
            </div>
        </div>

        <!-- 1. 選擇練舞地點 -->
        <div class="card sk-card sk-surface mb-4">
            <div class="card-header py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-white">
                    <i class="bi bi-sliders2-vertical me-2"></i>1. 選擇練舞地點
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-md-6 col-sm-8">
                        <label for="venue" class="form-label fw-semibold">
                            <i class="bi bi-geo-alt-fill me-1"></i>練舞地點
                        </label>
                        <select class="form-select" name="venue" id="venue">
                            <?php foreach ($venueGroups as $groupLabel => $group): ?>
                                <optgroup label="<?= htmlspecialchars($groupLabel) ?>">
                                    <?php foreach ($group as $name => $district): ?>
                                        <option value="<?= htmlspecialchars($name) ?>"
                                            <?= $name === $venue ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <span class="text-body-secondary small mt-1 d-inline-block">
                            <i class="bi bi-info-circle me-1"></i>行政區：<?= htmlspecialchars($district) ?>
                        </span>
                    </div>
                    <div class="col-md-3 col-sm-4">
                        <button type="submit" class="btn btn-brand w-100">
                            <i class="bi bi-search me-1"></i>查看預報
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. 預報結果 -->
        <?php if ($error): ?>
            <div id="error-alert" class="alert rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php elseif (empty($forecast)): ?>
            <div class="card sk-card sk-surface p-4 text-center">
                <p class="text-body-secondary mb-0">
                    <i class="bi bi-cloud-slash me-2"></i>氣象署目前只提供未來幾天的預報，剛好沒有落在平日晚上或假日白天的練習時段內，晚點再回來看看。
                </p>
            </div>
        <?php else: ?>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="card-title mb-0 fw-bold text-white">
                    <i class="bi bi-table me-2"></i>2. <?= htmlspecialchars($venue) ?> 練習時段預報
                </h5>
                <span class="chip">共 <?= count($forecast) ?> 個時段</span>
            </div>

            <div class="row g-4 mb-4">
                <?php foreach ($forecast as $f): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card sk-card sk-surface slot-card h-100">
                            <div class="card-header py-3 border-bottom text-body-secondary small d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-clock-fill me-1"></i><?= shortTime($f['start']) ?> － <?= shortTime($f['end']) ?></span>
                                <span class="badge badge-status <?= $f['isWeekend'] ? 'badge-status--done' : 'badge-status--pending' ?>">
                                    <?= $f['isWeekend'] ? '假日' : '平日' ?>
                                </span>
                            </div>
                            <div class="card-body p-4">
                                <p class="slot-wx mb-2"><?= htmlspecialchars($f['wx']) ?></p>
                                <p class="slot-nums text-body-secondary small mb-3">
                                    <span><i class="bi bi-thermometer-half"></i> <?= $f['minT'] ?>–<?= $f['maxT'] ?>°C</span><br>
                                    <span><i class="bi bi-umbrella-fill"></i> 降雨機率 <?= $f['pop'] ?>%</span><br>
                                    <span><i class="bi bi-emoji-smile-fill"></i> <?= htmlspecialchars($f['ci']) ?></span>
                                </p>
                                <div class="d-flex flex-column gap-2">
                                    <?php foreach (gearAdvice($f) as [$icon, $label, $text]): ?>
                                        <div class="tip-row">
                                            <i class="bi <?= $icon ?>" aria-hidden="true"></i>
                                            <span>
                                                <strong class="text-white d-block"><?= $label ?></strong>
                                                <span class="text-body-secondary small"><?= $text ?></span>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- 版權聲明 -->
        <section>
            <div class="card sk-card sk-surface p-4">
                <p class="text-body-secondary small mb-0">
                    天氣資料來自<a href="https://opendata.cwa.gov.tw/" target="_blank" rel="noopener">中央氣象署開放資料平臺</a>鄉鎮天氣預報，只挑平日晚上 5-10 點、假日早上 11 點到晚上 10 點這些練習時段顯示，僅供水晶熱舞社練舞場備參考，正式異動仍以氣象署最新發布為準。
                </p>
            </div>
        </section>

    </main>

    <footer>
        <p>SECHSKIES Fanpage &copy; 2026 - 黃色氣球永遠飄揚</p>
        <a href="https://jekkinoopy.github.io/Portfolio/" class="studio-link" target="_blank" rel="noopener noreferrer">
            Design by <span class="mark_b">Jekkinoopy Studio</span>
        </a>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/particles.js"></script>
</body>

</html>
