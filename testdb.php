<?php
// 顯示錯誤方便除錯
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 資料庫連線設定
$servername = "localhost:3307";
$dbUsername = "root";
$dbPassword = "41075925";
$dbname = "practice";

// 建立連線
$conn = mysqli_connect($servername, $dbUsername, $dbPassword, $dbname);
if (!$conn) {
    die("連線失敗: " . mysqli_connect_error());
}

// 先檢查資料表是否已經有資料（避免重複插入）
$result_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM job");
$row_check = mysqli_fetch_assoc($result_check);

if ($row_check['count'] == 0) {
    // SQL：一次插入 11 筆資料，自動編號
    $sql_insert = "INSERT INTO `job` (`company`, `content`, `pdate`) VALUES
    ('輔仁科技', '誠徵雲端工程師，三年工作經驗以上', '2025-10-18'),
    ('樹德資訊', '誠徵雲端工程師，一年工作經驗以上', '2025-10-19'),
    ('伯達資訊', '誠徵雲端工程師，三年工作經驗以上', '2025-10-20'),
    ('利瑪竇資訊', '誠徵雲端工程師，三年工作經驗以上', '2025-10-25'),
    ('輔雲科技', '誠徵雲端工程師，一年工作經驗以上', '2025-10-25'),
    ('輔雲科技', '誠徵程式設計師，一年工作經驗以上', '2025-10-25'),
    ('羅耀拉科技', '誠徵程式設計師，無經驗可。', '2025-10-31'),
    ('羅耀拉科技', '誠徵雲端工程師，無經驗可。', '2025-11-05'),
    ('樹德資訊', '誠徵專案經理，三年工作經驗以上。', '2025-11-05'),
    ('伯達資訊', '誠徵專案經理，三年工作經驗以上。', '2025-11-05'),
    ('羅耀拉科技', '誠徵專案經理，三年工作經驗以上。', '2025-11-07');";

    if (mysqli_query($conn, $sql_insert)) {
        echo "<p>初始資料已插入成功!</p>";
    } else {
        echo "錯誤: " . mysqli_error($conn);
    }
}

// 讀取所有資料
$sql_select = "SELECT * FROM job ORDER BY postid ASC";
$result = mysqli_query($conn, $sql_select);

if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "<p>";
        echo "編號: " . $row["postid"] . "<br>";
        echo "公司名稱: " . $row["company"] . "<br>";
        echo "職缺內容: " . $row["content"] . "<br>";
        echo "刊登日期: " . $row["pdate"];
        echo "</p>";
    }
} else {
    echo "查詢失敗: " . mysqli_error($conn);
}

// 關閉連線
mysqli_close($conn);
?>
