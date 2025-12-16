<?php
// chart_data.php
// --------------------------------------------------------
// 用途：產生 JSON 格式的統計資料供圖表 (Chart.js) 使用
// --------------------------------------------------------

// 引入資料庫連線設定
require 'db.php';

// 準備統計查詢 SQL
// 目標：計算每個活動 (Content) 有多少人報名 (Signup Count)
// 1. 使用 LEFT JOIN 連結 job (job) 與 applications (a) 資料表
//    這樣即使某個活動沒人報名 (a.job_id 為 NULL)，該活動也會顯示出來
// 2. COUNT(a.job_id) 計算關聯到的報名紀錄數量
// 3. GROUP BY j.postid, j.content 依據每個職缺分組
// 4. ORDER BY j.pdate ASC 依照刊登日期排序
$sql = "SELECT j.content, COUNT(a.job_id) AS signup_count
        FROM job j
        LEFT JOIN applications a ON j.postid = a.job_id
        GROUP BY j.postid, j.content
        ORDER BY j.pdate ASC";

// 執行查詢
$result = mysqli_query($conn, $sql);

// 初始化陣列來存放標籤 (X軸) 與數據 (Y軸)
$labels = [];
$data = [];

// 遍歷查詢結果
while ($row = mysqli_fetch_assoc($result)) {
    // 將活動內容加入標籤陣列
    $labels[] = $row['content'];

    // 將報名人數加入數據陣列
    // 強制轉型 (int) 確保是數字格式，便於前端 JS 運算
    $data[] = (int) $row['signup_count'];
}

// 輸出 JSON 格式
// json_encode 會將 PHP 陣列轉換為 JSON 字串
// 前端 AJAX 請求此檔案後，會收到如 {"labels":["活動A","活動B"], "data":[5, 12]} 的資料
echo json_encode(['labels' => $labels, 'data' => $data]);

// 關閉資料庫連線
mysqli_close($conn);
?>