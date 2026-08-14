<!doctype html>
<html lang="zh-TW">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>小黃集點卡管理｜水晶男孩推廣部 Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../../css/style.css">
</head>

<body>

  <nav class="navbar navbar-expand navbar-dark sk-navbar">
    <div class="container-fluid">
      <span class="navbar-brand mb-0"><i class="bi bi-gem me-1"></i>水晶男孩推廣部 <span class="fw-light">Admin</span></span>
      <div class="d-flex gap-2">
        <a class="btn btn-brand btn-sm" href="../index.html">回前台</a>
        <a class="btn btn-outline-brand btn-sm" href="../../practice-room.html">回水晶練習室</a>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">

      <aside class="col-lg-2 border-end sk-admin-sidebar py-4 px-3 min-vh-100">
        <nav class="nav flex-column">
          <a class="nav-link active" href="index.html"><i class="bi bi-person-hearts me-2"></i>小黃集點卡管理</a>
          <a class="nav-link disabled" href="#" aria-disabled="true"><i class="bi bi-megaphone me-2"></i>應援活動管理</a>
          <a class="nav-link disabled" href="#" aria-disabled="true"><i class="bi bi-envelope me-2"></i>聯絡資訊管理</a>
        </nav>
      </aside>

      <main class="col-lg-10 py-4 px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
          <h1 class="h3 mb-0 text-white">小黃集點卡管理</h1>
          <div class="d-flex gap-2">
            <a class="btn btn-brand" href="create.html"><i class="bi bi-plus-lg me-1"></i>新增粉絲資料</a>
            <a class="btn btn-outline-brand" href="../index.html">查看前台</a>
          </div>
        </div>

        <div class="card sk-card sk-surface">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>暱稱</th>
                  <th>入坑時間</th>
                  <th>應援物</th>
                  <th>應援事蹟</th>
                  <th>狀態</th>
                  <th class="text-end">操作</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>黃色氣球</td>
                  <td>20TH（2016）</td>
                  <td>手燈、初回版專輯、演唱會團服</td>
                  <td>20 週年台灣小黃串燒聯舞發起人之一，整理歷屆演唱會應援口號小抄。</td>
                  <td><span class="badge badge-status badge-status--done">公開</span></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-brand" href="edit.html">編輯</a>
                    <a class="btn btn-sm btn-outline-danger" href="delete.html">刪除</a>
                  </td>
                </tr>
                <tr>
                  <td>水晶老粉_TW</td>
                  <td>出道即入坑（1997）</td>
                  <td>初代應援手燈、簽名小卡收藏</td>
                  <td>保存至今最完整的台灣官方應援物收藏之一，多次借出給粉絲活動展示。</td>
                  <td><span class="badge badge-status badge-status--done">公開</span></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-brand" href="edit.html">編輯</a>
                    <a class="btn btn-sm btn-outline-danger" href="delete.html">刪除</a>
                  </td>
                </tr>
                <tr>
                  <td>熱舞應援組長</td>
                  <td>2018</td>
                  <td>應援毛巾、螢光棒</td>
                  <td>水晶熱舞社翻跳影片主要剪輯與應援手勢教學負責人。</td>
                  <td><span class="badge badge-status badge-status--done">公開</span></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-brand" href="edit.html">編輯</a>
                    <a class="btn btn-sm btn-outline-danger" href="delete.html">刪除</a>
                  </td>
                </tr>
                <tr>
                  <td>初心水晶迷</td>
                  <td>2023</td>
                  <td>手燈、周邊吊飾</td>
                  <td>30TH 應援企劃的新加入夥伴，正在製作第一份手工應援看板。</td>
                  <td><span class="badge badge-status badge-status--pending">草稿</span></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-brand" href="edit.html">編輯</a>
                    <a class="btn btn-sm btn-outline-danger" href="delete.html">刪除</a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>