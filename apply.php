<?php
// apply.php
// --------------------------------------------------------
// 用途：處理活動報名邏輯 (Action)
// 功能：
// 1. 檢查登入
// 2. 檢查重複報名
// 3. 寫入資料庫
// --------------------------------------------------------

// 1. 啟動 Session
session_start();

// 引入資料庫元件
require_once 'db.php';

// 2. 安全性檢查：確認使用者是否已登入
// 若未登入則無法報名，中止執行並顯示訊息
if (!isset($_SESSION['account'])) {
    die("請先登入才能報名。 <a href='index.php'>回首頁</a>");
}

// 3. 接收參數
// 從 GET 取得 postid (活動 ID)，若未設定則預設為 0
$postid = isset($_GET['postid']) ? intval($_GET['postid']) : 0;
// 從 Session 取得目前登入者的帳號
$account = $_SESSION['account'];

// 驗證 ID 合法性 (必需大於 0)
if ($postid <= 0) {
    die("無效的活動 ID。 <a href='index.php'>回首頁</a>");
}

// 4. 重複報名檢查
// 查詢 applications 表，看該帳號是否已對該 job_id 報名過
$check_sql = "SELECT id FROM applications WHERE job_id = $postid AND user_account = '$account'";
$check_result = mysqli_query($conn, $check_sql);

// 若查詢到的筆數大於 0，表示已報名
if (mysqli_num_rows($check_result) > 0) {
    // 顯示 JavaScript 警告視窗，然後跳轉回首頁
    echo "<script>
            alert('您已經報名過這個活動囉！');
            window.location.href = 'index.php';
          </script>";
    exit; // 停止執行
}

// 5. 執行報名寫入
// 將 job_id 與 user_account 插入 applications 表
$insert_sql = "INSERT INTO applications (job_id, user_account) VALUES ($postid, '$account')";

if (mysqli_query($conn, $insert_sql)) {
    // 報名成功
    // 導向成功畫面 apply_success.php，並帶上活動 postid 以便顯示詳情
    header("Location: apply_success.php?postid=$postid");
    exit;
} else {
    // 寫入失敗 (可能是 DB 錯誤)
    // 顯示詳細錯誤訊息
    echo "報名失敗: " . mysqli_error($conn);
    echo "<br><a href='index.php'>回首頁</a>";
}

// 關閉資料庫連線
mysqli_close($conn);
?>