<?php
// apply_success.php
// --------------------------------------------------------
// 用途：顯示報名成功訊息的頁面
// --------------------------------------------------------

// 1. 啟動 Session (獲取使用者與登入狀態)
session_start();

// 定義頁面標題
$title = "報名成功";

// 引入頁首
include "header.php";

// 引入資料庫連線 (為了查詢該活動的名稱以顯示給使用者看)
require_once 'db.php';

// 2. 獲取要顯示的活動 ID
// 從網址參數 postid 取得，若無則為 0
$postid = isset($_GET['postid']) ? intval($_GET['postid']) : 0;

// 初始化陣列，用於存放查到的活動資料
$job_info = [];

// 3. 查詢活動詳細資料 (若 ID 有效)
if ($postid > 0) {
    // 撰寫 SQL 查詢：根據 postid 找 company (廠商/營隊名) 與 content (內容/活動名)
    $sql = "SELECT company, content FROM job WHERE postid = $postid";

    // 執行查詢
    $result = mysqli_query($conn, $sql);

    // 取出第一筆資料 (理論上只會有一筆)
    if ($row = mysqli_fetch_assoc($result)) {
        $job_info = $row;
    }
}
?>

<div class="container mt-5">
    <!-- 使用 Bootstrap Card 元件製作美觀的訊息框 -->
    <div class="card text-center shadow">

        <!-- 卡片標頭：綠色背景白色文字 -->
        <div class="card-header bg-success text-white">
            <h3>🎉 恭喜！報名成功</h3>
        </div>

        <!-- 卡片內容區 -->
        <div class="card-body py-5">
            <h5 class="card-title text-success mb-4">您的申請已成功送出</h5>

            <!-- 如果有查到活動資料，才顯示詳細活動名稱 -->
            <?php if (!empty($job_info)): ?>
                <p class="card-text fs-5">
                    您報名的活動是：<br>
                    <!-- htmlspecialchars 處理特殊字元，防止 XSS -->
                    <strong><?= htmlspecialchars($job_info['company']) ?></strong> -
                    <span class="text-muted"><?= htmlspecialchars($job_info['content']) ?></span>
                </p>
            <?php endif; ?>

            <p class="text-muted">系統已將您的報名紀錄存入資料庫。</p>

            <!-- 操作按鈕區 -->
            <div class="mt-4">
                <a href="index.php" class="btn btn-primary btn-lg">回到活動列表</a>
                <a href="my_applications.php" class="btn btn-outline-primary btn-lg">查看我的報名</a>
            </div>
        </div>

        <!-- 卡片頁尾：顯示操作時間 -->
        <div class="card-footer text-muted">
            操作時間：<?= date('Y-m-d H:i:s') ?>
        </div>
    </div>
</div>

<?php
// 引入頁尾
include "footer.php";
?>