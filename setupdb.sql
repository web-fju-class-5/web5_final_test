-- setup_db.sql
-- 請在 XAMPP 的 phpMyAdmin 中執行此 SQL 檔案
-- 1. 訪問 http://localhost/phpmyadmin/
-- 2. 點擊 "SQL" 標籤
-- 3. 複製並貼上所有內容，然後點擊 "執行"

-- 建立資料庫 (如果 db.php 沒有自動建立的話)
CREATE DATABASE IF NOT EXISTS `activity_db` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 使用此資料庫
USE `activity_db`;

-- --------------------------------------------------------

--
-- 資料表結構：`events` (活動主表)
--
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '活動名稱',
  `organizer_name` varchar(100) NOT NULL COMMENT '主辦單位名稱',
  `description` text NOT NULL COMMENT '活動簡介',
  `start_date` date NOT NULL COMMENT '活動開始日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 插入範例資料到 `events`
--
INSERT INTO `events` (`id`, `name`, `organizer_name`, `description`, `start_date`) VALUES
(1, 'AI 程式設計入門工作坊', 'A 大學資訊工程學系', '專為高中生設計的 Python 與 AI 入門實作課程，從零開始學習機器學習。', '2025-07-15'),
(2, '全國高中生辯論營', 'B 高中辯論社', '為期三天的密集辯論訓練，邀請知名辯士指導。', '2025-07-20'),
(3, '生物科技實驗室體驗', 'C 科技公司', '參觀國家級實驗室，親手操作 DNA 萃取實驗。', '2025-08-01'),
(4, '偏鄉服務志工隊', 'D 基金會', '前往偏鄉國小進行為期一週的暑期課輔服務。', '2025-07-10'),
(5, '醫學系探索營', 'E 醫學大學', '深入了解醫學系課程、PBL 教學，並參觀大體實驗室。', '2025-07-25'),
(6, '建築設計模型實作', 'A 大學建築系', '學習基本圖學與模型製作技巧，完成你的第一個建築模型。', '2025-08-05');

-- --------------------------------------------------------

--
-- 資料表結構：`tags` (標籤表)
--
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '標籤名稱 (例如: 資訊、工程、醫藥衛生)',
  `type` varchar(50) NOT NULL COMMENT '標籤類型 (例如: 學群、學類、主辦單位)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_type` (`name`,`type`) COMMENT '名稱和類型組合必須是唯一的'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 插入範例資料到 `tags`
--
INSERT INTO `tags` (`id`, `name`, `type`) VALUES
(1, '資訊學群', '學群'),
(2, '工程學群', '學群'),
(3, '醫藥衛生學群', '學群'),
(4, '生命科學學群', '學群'),
(5, '社會與心理學群', '學群'),
(6, '建築與設計學群', '學群'),
(10, '工作坊', '學類'),
(11, '營隊', '學類'),
(12, '講座', '學類'),
(13, '志工服務', '學類'),
(14, '企業參訪', '學類'),
(20, 'A 大學', '主辦單位'),
(21, 'B 高中', '主辦單位'),
(22, 'C 科技公司', '主辦單位'),
(23, 'D 基金會', '主辦單位'),
(24, 'E 醫學大學', '主辦單位');


-- --------------------------------------------------------

--
-- 資料表結構：`event_tags` (中間表)
-- 這是多對多 (Many-to-Many) 關係的關鍵
--
CREATE TABLE `event_tags` (
  `event_id` int(11) NOT NULL COMMENT '對應到 events.id',
  `tag_id` int(11) NOT NULL COMMENT '對應到 tags.id',
  PRIMARY KEY (`event_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `event_tags_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 插入範例資料到 `event_tags` (建立活動和標籤的關聯)
--
INSERT INTO `event_tags` (`event_id`, `tag_id`) VALUES
(1, 1),  -- 活動1 (AI工作坊) 屬於 '資訊學群'
(1, 10), -- 活動1 (AI工作坊) 屬於 '工作坊'
(1, 20), -- 活動1 (AI工作坊) 屬於 'A 大學'
(2, 5),  -- 活動2 (辯論營) 屬於 '社會與心理學群'
(2, 11), -- 活動2 (辯論營) 屬於 '營隊'
(2, 21), -- 活動2 (辯論營) 屬於 'B 高中'
(3, 4),  -- 活動3 (生物科技) 屬於 '生命科學學群'
(3, 14), -- 活動3 (生物科技) 屬於 '企業參訪'
(3, 22), -- 活動3 (生物科技) 屬於 'C 科技公司'
(4, 5),  -- 活動4 (偏鄉服務) 屬於 '社會與心理學群'
(4, 13), -- 活動4 (偏鄉服務) 屬於 '志工服務'
(4, 23), -- 活動4 (偏鄉服務) 屬於 'D 基金會'
(5, 3),  -- 活動5 (醫學營) 屬於 '醫藥衛生學群'
(5, 11), -- 活動5 (醫學營) 屬於 '營隊'
(5, 24), -- 活動5 (醫學營) 屬於 'E 醫學大學'
(6, 6),  -- 活動6 (建築設計) 屬於 '建築與設計學群'
(6, 10), -- 活動6 (建築設計) 屬於 '工作坊'
(6, 20); -- 活動6 (建築設計) 屬於 'A 大學'