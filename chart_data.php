<?php
require 'db.php';

// 查詢報名人數，並取得對應活動名稱
$sql = "SELECT j.content, COUNT(a.job_id) AS signup_count
        FROM job j
        LEFT JOIN applications a ON j.postid = a.job_id
        GROUP BY j.postid, j.content
        ORDER BY j.pdate ASC";  // 可按日期排序

$result = mysqli_query($conn, $sql);

$labels = [];
$data = [];

while($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['content'];              // 活動名稱
    $data[] = (int)$row['signup_count'];      // 報名人數-整數
}

// 回傳 JSON 給前端 Chart.js
echo json_encode(['labels' => $labels, 'data' => $data]);

mysqli_close($conn);
?>
