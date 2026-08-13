# sechskies_fans — AI 工作上下文

> 這個檔案讓 AI 在任何裝置（家／學校）都能快速恢復上下文。
> **每次對話做了重要決定，請叫我更新這裡，然後 git commit。**

---

## 專案簡介

水晶男孩（Sechs Kies）粉絲「練習追蹤」獨立專案，2026-08-04 從主站 `sechskies` 拆出：
- `practice-room.html`（水晶練習室・**總覽**：四分館卡片＋未來擴充路線圖）
- `jekki-dance.html`（水晶熱舞社・**分館詳情頁**：介紹＋20TH-30TH 影片＋練習播放器＋練舞清單＋段落練習＋30TH 應援表單，全部在同一頁自然往下排）
- `admin/`（獨立後台：練舞清單 CRUD、30TH 應援報名處理、自己的管理者帳號與 MySQL）

拆分原因：水晶熱舞社、水晶練習室、偶像練習生原本是同一個「練習追蹤」概念，借用主站 `sechskies` 的共用後臺與資料庫，站主決定完全獨立成自己的 repo、資料庫與登入系統。

**結構演變（教訓紀錄，之後改版前務必先讀）**：站主要的「融合」從頭到尾都不等於「頁面數量」。走過三個錯誤版本：
1. 塞成單一 `index.html`，分館用分頁呈現——被打回票：拿掉了粒子背景、熱舞社原本一頁的份量被壓縮成分頁裡一小塊、30TH 表單被埋
2. 改回兩個對等頁面＋共用 navbar，只是重新上色——被打回票：這只是重新上色舊結構，不是真的融合，站主也強調沒有要求走回頭路
3. **現況（正確版）**：`practice-room.html` 自己的文案早就寫明底下有四個分館、熱舞社是其中一館——所以正確結構是**主從關係**：`practice-room.html` 是總覽（分館導覽台，熱舞社卡片有 CTA 連到詳情頁），`jekki-dance.html` 是熱舞社的完整分館詳情頁（播放器／練舞清單／段落練習都從 practice-room 搬過來，跟原有的影片、表單接在同一頁自然往下排，不是分頁也不是卡片）

兩頁統一套用「偶像練習生」的深色版型（`css/style.css`）與粒子背景（`assets/js/particles.js`），這部分是對的，沒有再改過。

`design-reference/sechskies-admin-hw/` 是另一份學校作業（純靜態、深色 Bootstrap 主題），視覺版型已正式套用到兩個前台頁面，`design-reference/` 資料夾保留原檔當歷史備份。

---

## 架構重點

- 前台兩頁，**主從關係非對等**：`practice-room.html`（總覽，四分館卡片，熱舞社卡片連到詳情頁）→ `jekki-dance.html`（熱舞社完整詳情：介紹、影片、播放器、練舞清單、段落練習、30TH 表單，一頁到底不分頁）；共用同一套深色版型（`css/style.css`）與粒子背景（`assets/js/particles.js`），頁首各自有一個極簡 `.sk-navbar` 互相連結＋連到後台
- `dance-practices-data.php` 的 fetch 呼叫只在 `jekki-dance.html`（練習播放器／練舞清單都歸屬熱舞社詳情頁，不在總覽頁重複）
- **拆分時移除了主站的 `portal-nav`（全站導覽）**：本專案頁面少，不需要主站等級的巨型導覽選單，改用兩頁互連的極簡 navbar
- 後台 `admin/` 沿用主站的通用 CRUD 引擎（`records.php`／`record-form.php`／`record-delete.php`，由 `bootstrap.php` 的 `modules()` 設定驅動），但 `modules()` 已精簡到只剩 `dance_practices` 一個模組；後台目前維持淺色 `admin.css`，尚未套用深色版型
- 資料庫：`admin/database/schema.sql`，包含 `admin_users`、`dance_applications`、`dance_practice_items`、`audit_logs`
- 上傳檔案目錄：`storage/dance-applications/`（`.htaccess` 已設 deny all）
- 部署：Render（Docker），`Dockerfile` 用 `php:8.2-apache` + `pdo_mysql`；Render 沒有管理式 MySQL，資料庫主機尚未決定（待確認）

---

## 目前工作狀態

**最後更新：** 2026-08-04

**進行中：**
- 無

**待確認：**
- 後台 `admin/` 尚未套用「偶像練習生」深色版型，維持淺色 admin.css
- Render 資料庫主機尚未決定（Render 無管理式 MySQL）
- 本機尚未建立 MySQL 資料庫、尚未跑過 `admin/setup.php` 建立第一個管理者帳號

**已完成：**
- 從主站 `sechskies` 拆分出前台頁面、後台 CRUD、資料庫 schema
- git init 並 push 到 github.com/jekkinoopy/sechskies_fans
- 補上 Render 部署用的 Dockerfile
- 兩頁統一套用偶像練習生深色版型＋粒子背景
- 改成主從結構：`practice-room.html`＝總覽、`jekki-dance.html`＝熱舞社完整詳情頁（播放器／練舞清單／段落練習從總覽頁搬到詳情頁，跟原有影片與 30TH 表單接在同一頁）
