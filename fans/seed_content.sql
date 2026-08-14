-- Fan backend training seed content
-- Run after creating the tables in your schema.

INSERT INTO fan_site_titles (image_path, alt_text, is_active) VALUES
('assets/titles/title_01_crystal_signal.png', '水晶男孩粉絲名單', 1),
('assets/titles/title_02_yellow_note.png', '黃色筆記・應援資料站', 0),
('assets/titles/title_03_fan_roster.png', '粉絲名單後台特訓', 0),
('assets/titles/title_04_support_station.png', '應援快訊站', 0);

INSERT INTO fan_marquee_texts (content, is_visible) VALUES
('六顆水晶，一起閃到最後。', 1),
('黃色燈海集合，今天也要好好應援。', 1),
('舊資料慢慢補，水晶回憶不下線。', 1),
('喜歡不設期限，應援持續更新。', 1);

INSERT INTO fan_banner_gifs (image_path, is_visible) VALUES
('assets/gifs/gif_01_yellow_signal.gif', 1),
('assets/gifs/gif_02_fan_chant.gif', 1),
('assets/gifs/gif_03_six_crystals.gif', 1),
('assets/gifs/gif_04_support_wave.gif', 1);

INSERT INTO fan_gallery_photos (image_path, is_visible, sort_order) VALUES
('assets/gallery/gallery_01_yellow_light.png', 1, 1),
('assets/gallery/gallery_02_ticket_memory.png', 1, 2),
('assets/gallery/gallery_03_support_board.png', 1, 3),
('assets/gallery/gallery_04_crystal_table.png', 1, 4);

INSERT INTO fan_news (content, is_visible) VALUES
('粉絲資料庫開始整理，先從公開可確認的活動與應援紀錄建檔。', 1),
('本週新增應援物精選相簿，後台可控制照片顯示狀態。', 1),
('黃色筆記資料站進行版面整理，舊資料會分批補上。', 1),
('粉絲名單新增草稿與公開狀態，未完成資料不會出現在前台。', 1),
('快訊頁新增完整列表與分頁功能，首頁保留最新五則。', 1),
('後台選單改為資料庫控制，主選單與次選單都可新增修改。', 1);

INSERT INTO fan_menus (label, link_url, is_visible, sort_order) VALUES
('管理登入', 'login.php', 1, 1),
('網站首頁', 'index.php', 1, 2);

INSERT INTO fan_submenus (menu_id, label, link_url, is_visible, sort_order)
SELECT id, '更多內容', 'news.php', 1, 1
FROM fan_menus
WHERE label='網站首頁'
LIMIT 1;

INSERT INTO fan_site_settings (setting_key, setting_value) VALUES
('visit_count','0'),
('footer_text','SECHSKIES Fanpage © 2026');

INSERT INTO fan_roster (nickname, fan_since, support_items, story, status) VALUES
('Yellow Nubi', '入坑時間待補', '手幅,燈牌,收藏卡', '整理舊資料與應援紀錄，協助粉絲站持續更新。', 'published'),
('Crystal May', '2016', '票根,照片,應援扇', '喜歡收藏活動紀錄，也會幫忙補齊活動資訊。', 'published'),
('Jekki Note', '2024', '小卡,貼紙', '以資料整理為主，將零散內容轉成可查詢紀錄。', 'draft'),
('Yellow Wave', '2019', '燈牌,手幅', '參與線下應援並整理自己的觀演筆記。', 'published');
