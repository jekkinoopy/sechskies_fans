CREATE DATABASE IF NOT EXISTS yellowkies CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE yellowkies;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(80) NOT NULL,
    email VARCHAR(190) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dance_applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nickname VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL,
    dance_years TINYINT UNSIGNED NULL,
    available_date DATE NULL,
    attended_20th ENUM('attended','watched_video','first_time') NULL,
    participate_content VARCHAR(255) NOT NULL,
    song VARCHAR(80) NULL,
    reference_file_name VARCHAR(100) NULL,
    reference_original_name VARCHAR(255) NULL,
    reference_mime_type VARCHAR(100) NULL,
    reference_file_size INT UNSIGNED NULL,
    message_30th TEXT NULL,
    status ENUM('new','contacted','confirmed','declined','archived') NOT NULL DEFAULT 'new',
    admin_notes TEXT NULL,
    submitted_ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dance_applications_status_created (status, created_at),
    INDEX idx_dance_applications_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dance_practice_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    tags VARCHAR(255) NULL,
    practice_focus TEXT NOT NULL,
    video_url VARCHAR(500) NULL,
    difficulty ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
    progress_status ENUM('not_started','practicing','review','completed') NOT NULL DEFAULT 'not_started',
    note_text VARCHAR(255) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('draft','coming_soon','published','archived') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dance_practice_order (status, sort_order, id),
    INDEX idx_dance_practice_title (title)
) ENGINE=InnoDB;

INSERT INTO dance_practice_items
    (title, tags, practice_focus, difficulty, progress_status, note_text, is_featured, sort_order, status)
SELECT 'Couple', 'Dance,Vocal', '副歌動作、隊形感、甜感表情', 'easy', 'not_started', '可作為第一首示範歌', 1, 10, 'published'
WHERE NOT EXISTS (SELECT 1 FROM dance_practice_items WHERE title = 'Couple');

INSERT INTO dance_practice_items
    (title, tags, practice_focus, difficulty, progress_status, note_text, is_featured, sort_order, status)
SELECT 'Com'' Back', 'Dance,Rap', '強拍、Rap 段落、舞台氣勢', 'medium', 'not_started', '未來擴充', 0, 20, 'coming_soon'
WHERE NOT EXISTS (SELECT 1 FROM dance_practice_items WHERE title = 'Com'' Back');

INSERT INTO dance_practice_items
    (title, tags, practice_focus, difficulty, progress_status, note_text, is_featured, sort_order, status)
SELECT 'Road Fighter', 'Dance,Rap,Performance', '節奏、力量、舞台感', 'hard', 'not_started', '未來擴充', 0, 30, 'coming_soon'
WHERE NOT EXISTS (SELECT 1 FROM dance_practice_items WHERE title = 'Road Fighter');

CREATE TABLE IF NOT EXISTS fan_roster (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nickname VARCHAR(80) NOT NULL,
    join_time VARCHAR(80) NOT NULL,
    fan_items VARCHAR(255) NULL,
    story TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('draft','coming_soon','published','archived') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fan_roster_order (status, sort_order, id)
) ENGINE=InnoDB;

INSERT INTO fan_roster
    (nickname, join_time, fan_items, story, sort_order, status)
SELECT '黃色氣球', '20TH（2016）', '手燈,初回版專輯,演唱會團服', '20 週年台灣小黃串燒聯舞發起人之一，整理了歷屆演唱會的應援口號小抄。', 10, 'published'
WHERE NOT EXISTS (SELECT 1 FROM fan_roster WHERE nickname = '黃色氣球');

INSERT INTO fan_roster
    (nickname, join_time, fan_items, story, sort_order, status)
SELECT '水晶老粉_TW', '出道即入坑（1997）', '初代應援手燈,簽名小卡收藏', '保存至今最完整的台灣官方應援物收藏之一，多次借出給粉絲活動展示。', 20, 'published'
WHERE NOT EXISTS (SELECT 1 FROM fan_roster WHERE nickname = '水晶老粉_TW');

INSERT INTO fan_roster
    (nickname, join_time, fan_items, story, sort_order, status)
SELECT '熱舞應援組長', '2018', '應援毛巾,螢光棒', '水晶熱舞社翻跳影片主要剪輯與應援手勢教學負責人。', 30, 'published'
WHERE NOT EXISTS (SELECT 1 FROM fan_roster WHERE nickname = '熱舞應援組長');

INSERT INTO fan_roster
    (nickname, join_time, fan_items, story, sort_order, status)
SELECT '初心水晶迷', '2023', '手燈,周邊吊飾', '30TH 應援企劃的新加入夥伴，正在製作第一份手工應援看板。', 40, 'draft'
WHERE NOT EXISTS (SELECT 1 FROM fan_roster WHERE nickname = '初心水晶迷');

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT UNSIGNED NULL,
    action_name VARCHAR(40) NOT NULL,
    entity_type VARCHAR(80) NOT NULL,
    entity_id INT UNSIGNED NULL,
    details_json JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_entity (entity_type, entity_id),
    CONSTRAINT fk_audit_admin FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
