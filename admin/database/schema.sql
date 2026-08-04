CREATE DATABASE IF NOT EXISTS sechskies_fans CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sechskies_fans;
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
