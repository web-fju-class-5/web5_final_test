-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1:3307
-- 產生時間： 2025-10-14 09:44:29
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
-- 資料表結構 `job`
--

CREATE TABLE `job` (
  `postid` int(11) NOT NULL,
  `company` varchar(45) NOT NULL,
  `content` text NOT NULL,
  `pdate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `job`
--

INSERT INTO `job` (`postid`, `company`, `content`, `pdate`) VALUES
(1, '輔仁科技', '誠徵雲端工程師，三年工作經驗以上', '2025-10-18'),
(2, '樹德資訊', '誠徵雲端工程師，一年工作經驗以上', '2025-10-19'),
(3, '伯達資訊', '誠徵雲端工程師，三年工作經驗以上', '2025-10-20'),
(4, '利瑪竇資訊', '誠徵雲端工程師，三年工作經驗以上', '2025-10-25'),
(5, '輔雲科技', '誠徵雲端工程師，一年工作經驗以上', '2025-10-25'),
(6, '輔雲科技', '誠徵程式設計師，一年工作經驗以上', '2025-10-25'),
(7, '羅耀拉科技', '誠徵程式設計師，無經驗可。', '2025-10-31'),
(8, '羅耀拉科技', '誠徵雲端工程師，無經驗可。', '2025-11-05'),
(9, '樹德資訊', '誠徵專案經理，三年工作經驗以上。', '2025-11-05'),
(10, '伯達資訊', '誠徵專案經理，三年工作經驗以上。', '2025-11-05'),
(11, '羅耀拉科技', '誠徵專案經理，三年工作經驗以上。', '2025-11-07');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `job`
--
ALTER TABLE `job`
  ADD PRIMARY KEY (`postid`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `job`
--
ALTER TABLE `job`
  MODIFY `postid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
