<?php
// apply_success.php
// --------------------------------------------------------
// 純顯示頁面：
// 顯示「報名成功」的綠色卡片，並列出剛剛報名的活動資訊。
// --------------------------------------------------------

session_start();
$title = "報名成功";
include "header.php";
require_once 'db.php';

// 獲取活動 ID 以顯示活動名稱
$postid = isset($_GET['postid']) ? intval($_GET['postid']) : 0;
$job_info = [];

// 查詢該活動的詳細資料
if ($postid > 0) {
    $sql = "SELECT company, content FROM job WHERE postid = $postid";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $job_info = $row;
    }
}
?>

<div class="container mt-5">
    <div class="card text-center shadow">
        <div class="card-header bg-success text-white">
            <h3>🎉 恭喜！報名成功</h3>
        </div>
        <div class="card-body py-5">
            <h5 class="card-title text-success mb-4">您的申請已成功送出</h5>
            
            <!-- 如果有查到活動資料，顯示出來 -->
            <?php if (!empty($job_info)): ?>
                <p class="card-text fs-5">
                    您報名的活動是：<br>
                    <strong><?= htmlspecialchars($job_info['company']) ?></strong> - 
                    <span class="text-muted"><?= htmlspecialchars($job_info['content']) ?></span>
                </p>
            <?php endif; ?>

            <p class="text-muted">系統已將您的報名紀錄存入資料庫。</p>
            
            <div class="mt-4">
                <a href="index.php" class="btn btn-primary btn-lg">回到活動列表</a>
                <a href="my_applications.php" class="btn btn-outline-primary btn-lg">查看我的報名</a>
            </div>
        </div>
        <div class="card-footer text-muted">
            操作時間：<?= date('Y-m-d H:i:s') ?>
        </div>
    </div>
</div>

<?php 
include "footer.php"; 
?>