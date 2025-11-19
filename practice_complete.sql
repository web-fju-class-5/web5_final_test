-- practice_complete.sql
-- 資料庫完整腳本：包含活動(event)、職缺(job)、使用者(user)、標籤(tags)、
-- 職缺標籤關聯(job_tags) 以及 報名紀錄(applications)

-- 設定 SQL 模式與時區
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 設定編碼
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 1. 建立並選擇資料庫
--
CREATE DATABASE IF NOT EXISTS `practice` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `practice`;

-- --------------------------------------------------------

--
-- 2. 資料表結構 `event` (一般活動表)
-- 用於儲存非職缺類的活動
--
CREATE TABLE IF NOT EXISTS `event` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '活動ID (主鍵)',
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
-- 3. 資料表結構 `job` (職缺/活動表)
-- 這是主要搜尋功能的目標資料表
--
CREATE TABLE IF NOT EXISTS `job` (
  `postid` int(11) NOT NULL AUTO_INCREMENT COMMENT '職缺ID (主鍵)',
  `company` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '公司或主辦單位名稱',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '職缺內容或活動詳情',
  `pdate` date NOT NULL COMMENT '刊登/活動日期',
  PRIMARY KEY (`postid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入 `job` 測試資料
INSERT INTO `job` (`postid`, `company`, `content`, `pdate`) VALUES
(1, '羅耀拉科技', 'AI工程師', '2025-10-28'),
(2, '樹德資訊', '誠徵雲端工程師，一年工作經驗以上', '2025-10-19'),
(3, '伯達資訊', '誠徵雲端工程師，三年工作經驗以上', '2025-10-20'),
(4, '利瑪竇資訊', '誠徵雲端工程師，三年工作經驗以上', '2025-10-25'),
(5, '輔雲科技', '誠徵雲端工程師，一年工作經驗以上', '2025-10-25'),
(6, '輔雲科技', '誠徵程式設計師，一年工作經驗以上', '2025-10-25'),
(7, '羅耀拉科技', '誠徵程式設計師，無經驗可。', '2025-10-31'),
(8, '羅耀拉科技', '誠徵雲端工程師，無經驗可。', '2025-11-05'),
(9, '樹德資訊', '誠徵專案經理，三年工作經驗以上。', '2025-11-05'),
(10, '伯達資訊', '誠徵專案經理，三年工作經驗以上。', '2025-11-05'),
(11, '羅耀拉科技', '誠徵專案經理，三年工作經驗以上。', '2025-11-07'),
(12, 'Apple', 'Programmer', '2019-08-29'),
(13, '羅耀拉科技', 'AI工程師', '2025-10-28');

-- --------------------------------------------------------

--
-- 4. 資料表結構 `tags` (標籤定義表)
-- 定義所有可用的標籤及其類型
--
CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '標籤ID (主鍵)',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '標籤名稱 (如: AI)',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '標籤類型 (如: 技能)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_type` (`name`,`type`) COMMENT '防止重複的標籤名稱與類型組合'
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
-- 5. 資料表結構 `job_tags` (職缺-標籤 中間表)
-- 多對多關聯：一個職缺可以有多個標籤，一個標籤可以屬於多個職缺
--
CREATE TABLE IF NOT EXISTS `job_tags` (
  `job_id` int(11) NOT NULL COMMENT '對應 job.postid',
  `tag_id` int(11) NOT NULL COMMENT '對應 tags.id',
  PRIMARY KEY (`job_id`,`tag_id`), -- 複合主鍵，避免重複關聯
  KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 建立外鍵約束 (Foreign Key Constraint)
-- 當刪除 job 或 tags 時，這裡對應的資料也會自動刪除 (CASCADE)
ALTER TABLE `job_tags`
  ADD CONSTRAINT `job_tags_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job` (`postid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `job_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 插入 `job_tags` 測試資料 (建立關聯)
INSERT INTO `job_tags` (`job_id`, `tag_id`) VALUES
(1, 1), (1, 11), (2, 2), (2, 11), (3, 2), (3, 12), (4, 2), (4, 12),
(5, 2), (5, 11), (6, 3), (6, 11), (7, 3), (7, 10), (8, 2), (8, 10),
(9, 4), (9, 12), (10, 4), (10, 12), (11, 4), (11, 12), (12, 3), (13, 1);

-- --------------------------------------------------------

--
-- 6. 資料表結構 `user` (使用者表)
--
CREATE TABLE IF NOT EXISTS `user` (
  `account` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '帳號 (主鍵)',
  `password` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密碼',
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '姓名',
  `role` char(1) CHARACTER SET ascii NOT NULL DEFAULT 'U' COMMENT '角色 (M:管理員, S:學生)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
  PRIMARY KEY (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入 `user` 測試資料
INSERT INTO `user` (`account`, `password`, `name`, `role`, `created_at`) VALUES
('admin', 'password', '管理員', 'M', '2025-10-28 14:50:43'),
('root', 'password', '管理員', 'T', '2025-10-28 14:13:38'),
('user1', 'pw1', '小明', 'S', '2025-10-28 14:13:38'),
('user2', 'pw2', '小華', 'S', '2025-10-28 14:13:38'),
('user3', 'pw3', '小美', 'S', '2025-10-28 14:13:38'),
('user4', 'pw4', '小強', 'S', '2025-10-28 14:13:38');

-- --------------------------------------------------------

--
-- 7. 資料表結構 `applications` (報名紀錄表)
-- 紀錄誰報名了哪個活動
--
CREATE TABLE IF NOT EXISTS `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '報名紀錄ID (主鍵)',
  `job_id` int(11) NOT NULL COMMENT '報名的職缺/活動ID',
  `user_account` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報名者帳號',
  `applied_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '報名時間',
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `user_account` (`user_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 建立關聯：刪除職缺或刪除使用者時，報名紀錄一併刪除
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job` (`postid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_account`) REFERENCES `user` (`account`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;