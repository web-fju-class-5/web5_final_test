-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- 主機: 127.0.0.1:3307
-- 產生時間： 2025-11-18 10:00
-- 伺服器版本: 10.4.27-MariaDB
-- PHP 版本： 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `practice`
--
CREATE DATABASE IF NOT EXISTS `practice` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `practice`;

-- --------------------------------------------------------

--
-- 資料表結構 `event`
-- (來自您 'practice (3).sql' 的資料)
--
CREATE TABLE `event` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾倒資料 `event`
--
INSERT INTO `event` (`id`, `name`, `description`) VALUES
(1, '迎新茶會', '迎新茶會是專為新生設計的交流活動，讓新同學能夠認識師長與學長姐，了解資管系的學習環境與資源。活動中有輕鬆的茶點、趣味破冰遊戲，以及學長姐經驗分享，幫助新生快速融入大學生活。'),
(2, '資管一日營', '資管一日營邀請大一新生透過一整天的活動更大學資管系的課程與生活。活動內容包含常用網站介紹、校園導覽與學長姐座談、闖關遊戲，讓參加者為未來四年作好準備。');

-- --------------------------------------------------------

--
-- 資料表結構 `job`
-- (來自您 'practice (3).sql' 的資料)
--
CREATE TABLE `job` (
  `postid` int(11) NOT NULL,
  `company` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `pdate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾倒資料 `job`
--
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
-- 資料表結構 `tags`
-- (新增的資料表，用於 'job' 的多重標籤搜尋)
--
CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '標籤名稱',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '標籤類型 (例如: 技能、經驗要求)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾倒資料 `tags`
--
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
-- 資料表結構 `job_tags`
-- (新增的中間表，用於連接 'job' 和 'tags')
--
CREATE TABLE `job_tags` (
  `job_id` int(11) NOT NULL COMMENT '對應到 job.postid',
  `tag_id` int(11) NOT NULL COMMENT '對應到 tags.id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾倒資料 `job_tags`
-- (將 'job' 資料表和 'tags' 資料表關聯起來)
--
INSERT INTO `job_tags` (`job_id`, `tag_id`) VALUES
(1, 1),
(1, 11),
(2, 2),
(2, 11),
(3, 2),
(3, 12),
(4, 2),
(4, 12),
(5, 2),
(5, 11),
(6, 3),
(6, 11),
(7, 3),
(7, 10),
(8, 2),
(8, 10),
(9, 4),
(9, 12),
(10, 4),
(10, 12),
(11, 4),
(11, 12),
(12, 3),
(13, 1);

-- --------------------------------------------------------

--
-- 資料表結構 `user`
-- (來自您 'practice (3).sql' 的資料)
--
CREATE TABLE `user` (
  `account` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` char(1) CHARACTER SET ascii NOT NULL DEFAULT 'U',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾倒資料 `user`
--
INSERT INTO `user` (`account`, `password`, `name`, `role`, `created_at`) VALUES
('admin', 'password', '管理員', 'M', '2025-10-28 14:50:43'),
('root', 'password', '管理員', 'T', '2025-10-28 14:13:38'),
('user1', 'pw1', '小明', 'S', '2025-10-28 14:13:38'),
('user2', 'pw2', '小華', 'S', '2025-10-28 14:13:38'),
('user3', 'pw3', '小美', 'S', '2025-10-28 14:13:38'),
('user4', 'pw4', '小強', 'S', '2025-10-28 14:13:38');

--
-- 已傾倒資料表的索引
--

--
-- 資料表索引 `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `job`
--
ALTER TABLE `job`
  ADD PRIMARY KEY (`postid`);

--
-- 資料表索引 `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_type` (`name`,`type`);

--
-- 資料表索引 `job_tags`
--
ALTER TABLE `job_tags`
  ADD PRIMARY KEY (`job_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- 資料表索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`account`);

--
-- 在傾倒的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `event`
--
ALTER TABLE `event`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `job`
--
ALTER TABLE `job`
  MODIFY `postid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 已傾倒資料表的限制式
--

--
-- 資料表的限制式 `job_tags`
--
ALTER TABLE `job_tags`
  ADD CONSTRAINT `job_tags_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job` (`postid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `job_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;