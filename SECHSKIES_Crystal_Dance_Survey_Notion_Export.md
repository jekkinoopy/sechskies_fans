# 水晶熱舞社頁面改版對話紀錄

> 來源：`yellow-note/crystal-dance-survey.html` 改版討論  
> 日期：2026-07-09  
> 用途：可貼上 Notion 的整理稿

---

## 一、任務目標

把 **水晶熱舞社** 頁（`yellow-note/crystal-dance-survey.html`）從「自創黃框、多層卡片、閱讀擁擠」改成：

1. **對齊首頁／全站簡約風格**（黑底 + 白卡片 + 品牌黃）
2. **多套用全站 CSS**（`style.css` 的 `section`、`.section-title`、`.card` 語彙）
3. **熱舞社相關內容不刪**（可改外觀與排版）
4. **Bootstrap class 必須保留**（作業要求）
5. **表單區參考「見面會應援調查表」的 summer-form 邊框排版**，顏色仍用全站黃色 token

---

## 二、核心原則（這次定下來的）

| 原則 | 說明 |
|------|------|
| 內容優先 | 跟熱舞社相關的資訊不要刪；外觀排版可以改 |
| 只改被點名的頁 | 主要動 `crystal-dance-survey.html` |
| Bootstrap 保留 | `row` / `col` / `form-control` / `form-select` / `btn-check` / `ratio` 等 class 留下 |
| 顏色不動 | 用全站 `--color-brand` 系列，不改成參考圖的藍 |
| 美感對齊 | 參考 index 簡約乾淨 + 參考圖的留白與分階段框線 |

---

## 三、舊版問題診斷

### 為什麼「怎麼看都不順眼」

1. **雙重／三重卡片**  
   全站 `section` 本身已是圓角白卡片，頁內又加 `video-card`、`song-card`、`form-panel` → 盒子套盒子。

2. **約 400 行 inline CSS 重做設計系統**  
   全站已有 `.card`、`.section-title`，卻自創多套黃框樣式。

3. **`info-card` 深色漸層突兀**  
   跟全站暖白、輕盈感相反。

4. **導覽太吵**  
   五顆黃色 pill 按鈕搶眼；首頁錨點是低調文字列。

5. **UX 問題（後續回饋）**  
   - 表單左右分欄難讀  
   - 標題跟內容距離太近  
   - 主影片寬度沒跟下方三支影片左右對齊  
   - 文字擠在一起、閱讀節奏差

---

## 四、結構重組（6 區 → 3 區）

### 備份

```
yellow-note/crystal-dance-survey.backup-2026-07-09.html
```

（本地備份；改動前版本）

### 新架構

| 新區塊 | ID | 合併來源 | 合併理由 |
|--------|-----|----------|----------|
| ① 認識水晶熱舞社 | `#about` | Hero + 熱舞社介紹 | 都在回答「這是什麼」 |
| ② 從 20TH 到 30TH | `#from-20th-to-30th` | 20TH 影片回顧 + 30TH 應援募集敘事 | 同一條時間軸：過去記憶 → 未來募集 |
| ③ 30TH 應援募集表單 | `#form-30th` | 表單區獨立 | Bootstrap 表單作業清楚；敘事講完才行動 |

### 移除

- 整個 **經典舞曲區**（`#song-archive` 四張 song-card）
- 歌曲資訊不丟：表單「想練的歌曲」`<select>` 仍保留 Couple / Com' Back / Road Fighter / Pom Saeng Pom Sa / 其他

---

## 五、視覺與版型決策

### 對齊全站

- 骨架：`inner-page-header` → `portal-nav` → `particles` → `container` → `section`
- 大區塊靠全站 `section` 白卡片浮在星空底上
- 避免 section 內再套一層 `.card`（影片／表單尤甚）

### 影片區

- 主影片與下方三支延伸影片包在同一 `video-grid`
- **主影片寬度與下方三欄左右切齊**（拿掉主影片單獨 `max-width: 720px` 置中）

### 間距與閱讀

- `.section-title` 與內文拉開（約 `2rem`）
- 段落行高約 `1.9`、段間距加大
- 子標題（延伸閱讀、30TH）上方留白加大，避免貼住上一段

---

## 六、表單：summer-form 風格

參考「見面會應援調查表」的**排版結構**，**顏色維持全站黃**：

### 結構

1. **填寫須知**（淡黃底邊框盒）
2. **第一階段｜參與者基本資料**（暱稱、Email、練舞年數、可參與日期）
3. **第二階段｜30TH 參與意向**（20TH 經驗、參與內容複選）
4. **第三階段｜應援內容與祝福**（歌曲、上傳、留言）
5. **底部按鈕**（重新填寫 / 送出並排）

### 排版特徵

- 階段標題在邊框盒**上方**、左對齊
- 每階段獨立 `summer-form__stage-body`（圓角細邊框）
- label 在上、輸入在下（單欄垂直流）
- 不用表單左右分欄
- Bootstrap class 全保留

### Class 命名（頁面專用）

- `.summer-form`
- `.summer-form__stage`
- `.summer-form__stage-title`
- `.summer-form__stage-body`
- `.summer-form__stage-body--notice`
- `.summer-form__option-row`
- `.summer-form__actions`

---

## 七、必須保留的 Bootstrap class（作業）

```html
row / col-* / g-*
form-label / form-control / form-select / form-text
btn-check / btn
ratio / ratio-16x9
d-flex / flex-wrap / gap-* / mt-*
```

---

## 八、Git 狀態（對話當時）

- Commit：`a977e78`  
  `refactor: 水晶熱舞社頁面重組為三區並對齊全站簡約風格`
- 已 push 至 `origin/main`（後續還有本地再改：間距、影片對齊、summer-form）
- 後續的 summer-form／間距微調若尚未再 commit，需另開 commit

---

## 九、給下次改這頁的檢查清單

- [ ] 熱舞社敘事、20TH 影片、30TH 募集說明、表單欄位是否都在
- [ ] 是否又出現「section 外套 + 內層黃框」雙重卡片
- [ ] 主影片是否與下方三支左右對齊
- [ ] 表單是否單欄分階段（summer-form），沒有左右表單／須知分欄
- [ ] 標題與內文、區塊與區塊是否有足夠留白
- [ ] Bootstrap 必要 class 是否還在
- [ ] 顏色是否仍用 `--color-brand` 系列（沒偷換成藍色）

---

## 十、一句話總結

> **讓 Bootstrap 負責格線與表單元件，讓 `style.css` 負責全站美感；頁面專用 CSS 只補缺口。內容依敘事合三區，表單用 summer-form 分階段邊框，顏色不動。**
