<?php
// apply.php
// --------------------------------------------------------
// 後端處理程式：
// 1. 檢查使用者登入狀態
// 2. 檢查是否已重複報名
// 3. 將報名資料寫入 `applications` 資料表
// --------------------------------------------------------

session_start();
require_once 'db.php';

// 1. 安全檢查：確認使用者是否已登入
if (!isset($_SESSION['account'])) {
    // 若未登入，顯示錯誤並引導回首頁
    die("請先登入才能報名。 <a href='index.php'>回首頁</a>");
}

// 2. 獲取 GET 參數：要報名的活動 ID (postid)
$postid = isset($_GET['postid']) ? intval($_GET['postid']) : 0;
$account = $_SESSION['account']; // 當前使用者

// 驗證 ID 合法性
if ($postid <= 0) {
    die("無效的活動 ID。 <a href='index.php'>回首頁</a>");
}

// 3. 邏輯檢查：確認是否「已經報名過」
$check_sql = "SELECT id FROM applications WHERE job_id = $postid AND user_account = '$account'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    // 若已存在紀錄，提示使用者並返回
    echo "<script>
            alert('您已經報名過這個活動囉！');
            window.location.href = 'index.php';
          </script>";
    exit;
}

// 4. 執行寫入：插入 applications 表
$insert_sql = "INSERT INTO applications (job_id, user_account) VALUES ($postid, '$account')";

if (mysqli_query($conn, $insert_sql)) {
    // 5. 成功：導向成功頁面
    header("Location: apply_success.php?postid=$postid");
    exit;
} else {
    // 失敗：顯示 SQL 錯誤
    echo "報名失敗: " . mysqli_error($conn);
    echo "<br><a href='index.php'>回首頁</a>";
}

mysqli_close($conn);
?>