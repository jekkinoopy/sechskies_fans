# sechskies_fans — AI 工作上下文

> 這個檔案讓 AI 在任何裝置（家／學校）都能快速恢復上下文。
> **每次對話做了重要決定，請叫我更新這裡，然後 git commit。**

---

## 專案簡介

水晶男孩（Sechs Kies）粉絲「練習追蹤」獨立專案，2026-08-04 從主站 `sechskies` 拆出：
- `crystal-dance-survey.html`（水晶熱舞社・30TH 應援報名表單）
- `practice-room.html`（水晶練習室・練舞清單與練習播放器）
- `admin/`（獨立後台：練舞清單 CRUD、30TH 應援報名處理、自己的管理者帳號與 MySQL）

拆分原因：這三個東西是同一個「練習追蹤」概念，原本借用主站 `sechskies` 的共用後臺與資料庫，站主決定完全獨立成自己的 repo、資料庫與登入系統，不再與主站共用任何資料表。

`design-reference/sechskies-admin-hw/` 是另一份學校作業（純靜態、深色 Bootstrap 主題），視覺版型是站主喜歡、想沿用的方向，目前只搬進來當參考素材，**尚未套用**到本專案的實際頁面上。

---

## 架構重點

- 前台頁面在專案根目錄：`crystal-dance-survey.html`、`practice-room.html`
- 共用資源複製自主站：`assets/css/style.css`（顏色 token）、`assets/css/tablet.css`、`assets/js/particles.js`
- **拆分時移除了主站的 `portal-nav`（全站導覽）**：本專案頁面只有兩頁，暫不需要主站等級的巨型導覽選單，之後如需要站內導覽再另外設計
- 後台 `admin/` 沿用主站的通用 CRUD 引擎（`records.php`／`record-form.php`／`record-delete.php`，由 `bootstrap.php` 的 `modules()` 設定驅動），但 `modules()` 已精簡到只剩 `dance_practices` 一個模組
- 資料庫：`admin/database/schema.sql`，包含 `admin_users`、`dance_applications`、`dance_practice_items`、`audit_logs`
- 上傳檔案目錄：`storage/dance-applications/`（`.htaccess` 已設 deny all）

---

## 目前工作狀態

**最後更新：** 2026-08-04

**進行中：**
- 無

**待確認：**
- 尚未套用「偶像練習生」深色版型（`design-reference/sechskies-admin-hw/`）到實際頁面
- 本機尚未建立 MySQL 資料庫、尚未跑過 `admin/setup.php` 建立第一個管理者帳號
- 尚未 `git init` / 建立 GitHub repo

**已完成：**
- 從主站 `sechskies` 拆分出前台頁面、後台 CRUD、資料庫 schema
