-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1:3307
-- 產生時間： 2025-12-02 07:18:15
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

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

-- --------------------------------------------------------

--
-- 資料表結構 `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL COMMENT '流水號主鍵',
  `job_id` int(11) NOT NULL COMMENT '報名的職缺/活動ID',
  `user_account` varchar(20) NOT NULL COMMENT '報名者帳號',
  `applied_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '報名時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `event`
--

CREATE TABLE `event` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '活動ID (自動遞增主鍵)',
  `name` varchar(30) NOT NULL COMMENT '活動名稱',
  `description` varchar(255) NOT NULL COMMENT '活動描述'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `event`
--

INSERT INTO `event` (`id`, `name`, `description`) VALUES
(1, '迎新茶會', '迎新茶會是專為新生設計的交流活動...'),
(2, '資管一日營', '資管一日營邀請大一新生透過一整天的活動...');

-- --------------------------------------------------------

--
-- 資料表結構 `job`
--

CREATE TABLE `job` (
  `postid` int(11) NOT NULL COMMENT '職缺/活動ID (主鍵)',
  `company` varchar(45) NOT NULL COMMENT '公司或主辦單位名稱',
  `content` text NOT NULL COMMENT '職缺內容或活動詳情',
  `pdate` date NOT NULL COMMENT '刊登日期或活動日期'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `job`
--

INSERT INTO `job` (`postid`, `company`, `content`, `pdate`) VALUES
(1, '中央大學', '醫學與健康科普體驗營', '2025-10-28'),
(3, '淡江大學', '體適能訓練營', '2025-10-20'),
(4, '中山大學', '脫口秀與自信表達營', '2025-10-25'),
(5, '台灣科技大學', '電子音樂與創作營', '2025-10-25'),
(6, '高雄科技大學', '戶外生存與團隊合作挑戰營', '2025-10-25'),
(7, '政治大學', '文創與攝影設計營', '2025-10-31'),
(8, '清華大學', '財經與投資模擬營', '2025-11-05'),
(9, '交通大學', '商業企劃與行銷創意營', '2025-11-05'),
(10, '成功大學', '心理學探索與人格測試營', '2025-11-05'),
(11, '台灣大學', '程式設計與 AI 創客營', '2025-11-07'),
(13, '中正大學', '法律思辨與模擬法庭營', '2025-10-28');

-- --------------------------------------------------------

--
-- 資料表結構 `job_tags`
--

CREATE TABLE `job_tags` (
  `job_id` int(11) NOT NULL COMMENT '對應 job.postid',
  `tag_id` int(11) NOT NULL COMMENT '對應 tags.id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `job_tags`
--

INSERT INTO `job_tags` (`job_id`, `tag_id`) VALUES
(1, 1),
(1, 11),
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
(13, 1);

-- --------------------------------------------------------

--
-- 資料表結構 `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL COMMENT '通知ID',
  `user_account` varchar(20) NOT NULL COMMENT '接收者帳號',
  `subject` varchar(100) NOT NULL COMMENT '通知標題',
  `message` text NOT NULL COMMENT '通知內容',
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT '已讀狀態 (0:未讀, 1:已讀)',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '發送時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL COMMENT '標籤ID (主鍵)',
  `name` varchar(50) NOT NULL COMMENT '標籤名稱 (顯示用)',
  `type` varchar(50) NOT NULL COMMENT '標籤類型 (分類用，如：技能)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `tags`
--

INSERT INTO `tags` (`id`, `name`, `type`) VALUES
(1, 'AI', '技能'),
(11, '一年經驗', '經驗要求'),
(12, '三年經驗', '經驗要求'),
(4, '專案管理', '技能'),
(10, '無經驗可', '經驗要求'),
(3, '程式設計', '技能'),
(2, '雲端', '技能');

-- --------------------------------------------------------

--
-- 資料表結構 `user`
--

CREATE TABLE `user` (
  `account` varchar(20) NOT NULL COMMENT '帳號 (主鍵)',
  `password` varchar(20) NOT NULL COMMENT '密碼 (明文存儲，實務上建議加密)',
  `name` varchar(20) NOT NULL COMMENT '姓名',
  `role` char(1) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'U' COMMENT '角色權限 (M:管理員, T:老師, S:學生, U:一般)',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT '建立時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `user`
--

INSERT INTO `user` (`account`, `password`, `name`, `role`, `created_at`) VALUES
('admin', 'password', '管理員', 'M', '2025-10-28 14:50:43'),
('root', 'password', '總管理員', 'T', '2025-10-28 14:13:38'),
('user1', 'pw1', '小明', 'S', '2025-10-28 14:13:38'),
('user2', 'pw2', '小華', 'S', '2025-10-28 14:13:38'),
('user3', 'pw3', '小美', 'S', '2025-10-28 14:13:38'),
('user4', 'pw4', '小強', 'S', '2025-10-28 14:13:38');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `user_account` (`user_account`);

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
-- 資料表索引 `job_tags`
--
ALTER TABLE `job_tags`
  ADD PRIMARY KEY (`job_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- 資料表索引 `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_account` (`user_account`);

--
-- 資料表索引 `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_type` (`name`,`type`);

--
-- 資料表索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`account`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '流水號主鍵';

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `event`
--
ALTER TABLE `event`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '活動ID (自動遞增主鍵)', AUTO_INCREMENT=3;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `job`
--
ALTER TABLE `job`
  MODIFY `postid` int(11) NOT NULL AUTO_INCREMENT COMMENT '職缺/活動ID (主鍵)', AUTO_INCREMENT=14;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '通知ID';

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '標籤ID (主鍵)', AUTO_INCREMENT=13;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job` (`postid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_account`) REFERENCES `user` (`account`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `job_tags`
--
ALTER TABLE `job_tags`
  ADD CONSTRAINT `job_tags_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job` (`postid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `job_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_account`) REFERENCES `user` (`account`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
