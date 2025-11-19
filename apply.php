<?php
// apply.php
// 後端處理程式：負責將使用者(User)與活動(Job)的關聯寫入 applications 資料表

session_start();
require_once 'db.php';

// 1. 安全檢查：確認使用者是否已登入
if (!isset($_SESSION['account'])) {
    // 若未登入，顯示錯誤並引導回首頁 (實際應用應導向登入頁)
    die("請先登入才能報名。 <a href='index.php'>回首頁</a>");
}

// 2. 獲取 GET 參數：要報名的活動 ID (postid)
// intval() 強制轉為整數，防止 SQL Injection
$postid = isset($_GET['postid']) ? intval($_GET['postid']) : 0;
$account = $_SESSION['account']; // 從 Session 獲取當前使用者帳號

// 簡單驗證 ID 是否合法
if ($postid <= 0) {
    die("無效的活動 ID。 <a href='index.php'>回首頁</a>");
}

// 3. 邏輯檢查：確認使用者是否「已經報名過」此活動
// 避免重複插入資料
$check_sql = "SELECT id FROM applications WHERE job_id = $postid AND user_account = '$account'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    // 如果查詢結果大於 0，表示已報名過
    echo "<script>
            alert('您已經報名過這個活動囉！');
            window.location.href = 'index.php'; // 轉址回首頁
          </script>";
    exit; // 結束程式
}

// 4. 執行寫入：將資料插入 applications 表
// id 為自動遞增，applied_at 為自動當下時間，故只需插入 job_id 和 user_account
$insert_sql = "INSERT INTO applications (job_id, user_account) VALUES ($postid, '$account')";

if (mysqli_query($conn, $insert_sql)) {
    // 5. 成功處理
    // 使用 header() 進行轉址，導向成功頁面，並帶上 ID 以便顯示資訊
    header("Location: apply_success.php?postid=$postid");
    exit;
} else {
    // 失敗處理
    echo "報名失敗: " . mysqli_error($conn);
    echo "<br><a href='index.php'>回首頁</a>";
}

mysqli_close($conn);
?>