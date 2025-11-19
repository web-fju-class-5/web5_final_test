-- practice_complete.sql
-- --------------------------------------------------------
-- 這是完整的資料庫建置腳本。
-- 包含以下資料表：
-- 1. event (一般活動)
-- 2. job (職缺/活動主表)
-- 3. user (使用者資料，含 email 與角色)
-- 4. tags (標籤定義)
-- 5. job_tags (職缺與標籤的多對多關聯)
-- 6. applications (使用者報名紀錄)
-- 7. notifications (站內通知紀錄)
-- --------------------------------------------------------

-- 設定 SQL 模式，避免某些嚴格檢查導致舊語法報錯
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- 開啟交易模式
START TRANSACTION;
-- 設定時區
SET time_zone = "+00:00";

-- 設定編碼為 utf8mb4 以支援中文
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 1. 建立並選擇資料庫 `practice`
-- IF NOT EXISTS: 避免重複建立導致錯誤
--
CREATE DATABASE IF NOT EXISTS `practice` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `practice`;

-- --------------------------------------------------------

--
-- 2. 資料表結構 `event`
-- 用途：儲存單純的活動資訊 (此範例中較少使用，主要邏輯在 job 表)
--
DROP TABLE IF EXISTS `event`; -- 若已存在則刪除，確保結構最新
CREATE TABLE `event` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '活動ID (自動遞增主鍵)',
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '活動名稱',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '活動描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入 `event` 測試資料
INSERT INTO `event` (`id`, `name`, `description`) VALUES
(1, '迎新茶會', '迎新茶會是專為新生設計的交流活動...'),
(2, '資管一日營', '資管一日營邀請大一新生透過一整天的活動...');

-- --------------------------------------------------------

--
-- 3. 資料表結構 `job`
-- 用途：系統核心資料表，儲存「職缺」或「活動」的詳細資訊
--
DROP TABLE IF EXISTS `job`;
CREATE TABLE `job` (
  `postid` int(11) NOT NULL AUTO_INCREMENT COMMENT '職缺/活動ID (主鍵)',
  `company` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '公司或主辦單位名稱',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '職缺內容或活動詳情',
  `pdate` date NOT NULL COMMENT '刊登日期或活動日期',
  PRIMARY KEY (`postid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入 `job` 測試資料
INSERT INTO `job` (`postid`, `company`, `content`, `pdate`) VALUES
(1, '羅耀拉科技', 'AI工程師', '2025-10-28'),
(2, '樹德資訊', '誠徵雲端工程師', '2025-10-19'),
(3, '伯達資訊', '誠徵雲端工程師', '2025-10-20'),
(4, '利瑪竇資訊', '誠徵雲端工程師', '2025-10-25'),
(5, '輔雲科技', '誠徵雲端工程師', '2025-10-25'),
(6, '輔雲科技', '誠徵程式設計師', '2025-10-25'),
(7, '羅耀拉科技', '誠徵程式設計師', '2025-10-31'),
(8, '羅耀拉科技', '誠徵雲端工程師', '2025-11-05'),
(9, '樹德資訊', '誠徵專案經理', '2025-11-05'),
(10, '伯達資訊', '誠徵專案經理', '2025-11-05'),
(11, '羅耀拉科技', '誠徵專案經理', '2025-11-07'),
(12, 'Apple', 'Programmer', '2019-08-29'),
(13, '羅耀拉科技', 'AI工程師', '2025-10-28');

-- --------------------------------------------------------

--
-- 4. 資料表結構 `tags`
-- 用途：定義系統中所有可用的標籤 (如：技能類、經驗類)
--
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '標籤ID (主鍵)',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '標籤名稱 (顯示用)',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '標籤類型 (分類用，如：技能)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_type` (`name`,`type`) -- 複合唯一鍵，防止同一類型下有重複名稱
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入 `tags` 測試資料
INSERT INTO `tags` (`id`, `name`, `type`) VALUES
(1, 'AI', '技能'),
(2, '雲端', '技能'),
(3, '程式設計', '技能'),
(4, '專案管理', '技能'),
(10, '無經驗可', '經驗要求'),
(11, '一年經驗', '經驗要求'),
(12, '三年經驗', '經驗要求');

-- --------------------------------------------------------

--
-- 5. 資料表結構 `job_tags`
-- 用途：中間表，實現 Job 和 Tags 的多對多 (Many-to-Many) 關係
--
DROP TABLE IF EXISTS `job_tags`;
CREATE TABLE `job_tags` (
  `job_id` int(11) NOT NULL COMMENT '對應 job.postid',
  `tag_id` int(11) NOT NULL COMMENT '對應 tags.id',
  PRIMARY KEY (`job_id`,`tag_id`), -- 複合主鍵，確保同一職缺不會重複標記相同標籤
  KEY `tag_id` (`tag_id`) -- 建立索引以加速查詢
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入 `job_tags` 關聯資料
INSERT INTO `job_tags` (`job_id`, `tag_id`) VALUES
(1, 1), (1, 11), (2, 2), (2, 11), (3, 2), (3, 12), (4, 2), (4, 12),
(5, 2), (5, 11), (6, 3), (6, 11), (7, 3), (7, 10), (8, 2), (8, 10),
(9, 4), (9, 12), (10, 4), (10, 12), (11, 4), (11, 12), (12, 3), (13, 1);

-- --------------------------------------------------------

--
-- 6. 資料表結構 `user`
-- 用途：儲存使用者帳號資訊
--
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `account` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '帳號 (主鍵)',
  `password` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密碼 (明文存儲，實務上建議加密)',
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '姓名',
  `role` char(1) CHARACTER SET ascii NOT NULL DEFAULT 'U' COMMENT '角色權限 (M:管理員, T:老師, S:學生, U:一般)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
  PRIMARY KEY (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入 `user` 測試資料
INSERT INTO `user` (`account`, `password`, `name`, `role`, `created_at`) VALUES
('admin', 'password', '管理員', 'M', '2025-10-28 14:50:43'),
('root', 'password', '總管理員', 'T', '2025-10-28 14:13:38'),
('user1', 'pw1', '小明', 'S', '2025-10-28 14:13:38'),
('user2', 'pw2', '小華', 'S', '2025-10-28 14:13:38'),
('user3', 'pw3', '小美', 'S', '2025-10-28 14:13:38'),
('user4', 'pw4', '小強', 'S', '2025-10-28 14:13:38');

-- --------------------------------------------------------

--
-- 7. 資料表結構 `applications`
-- 用途：紀錄使用者報名了哪些活動
--
DROP TABLE IF EXISTS `applications`;
CREATE TABLE `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '流水號主鍵',
  `job_id` int(11) NOT NULL COMMENT '報名的職缺/活動ID',
  `user_account` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報名者帳號',
  `applied_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '報名時間',
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `user_account` (`user_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 設定 applications 的外鍵約束 (Foreign Key)
-- ON DELETE CASCADE: 當 job 或 user 被刪除時，相關的報名紀錄也會自動刪除
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job` (`postid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_account`) REFERENCES `user` (`account`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- 8. 資料表結構 `notifications`
-- 用途：站內通知系統，儲存發送給使用者的訊息
--
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '通知ID',
  `user_account` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '接收者帳號',
  `subject` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '通知標題',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '通知內容',
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT '已讀狀態 (0:未讀, 1:已讀)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '發送時間',
  PRIMARY KEY (`id`),
  KEY `user_account` (`user_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 設定 notifications 的外鍵約束
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_account`) REFERENCES `user` (`account`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- 9. 補上 job_tags 的外鍵
-- (放在最後執行，確保參照的 table 都已建立)
--
ALTER TABLE `job_tags`
  ADD CONSTRAINT `job_tags_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job` (`postid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `job_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 提交交易
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;