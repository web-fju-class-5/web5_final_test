<?php
// db.php
// 負責建立與 MySQL 資料庫的連線
// 所有需要存取資料庫的頁面都必須 include 這個檔案

// 資料庫連線參數
$servername = "localhost"; // 主機名稱 (XAMPP 預設為 localhost)
$username = "root";        // 資料庫使用者名稱 (XAMPP 預設為 root)
$password = "";            // 資料庫密碼 (XAMPP 預設為空)
$dbname = "practice";      // 資料庫名稱 (必須與 practice_complete.sql 中建立的名稱一致)

// 建立連線物件 $conn
$conn = mysqli_connect($servername, $username, $password, $dbname);

// 檢查連線是否成功
// 如果連線失敗， mysqli_connect_error() 會回傳錯誤訊息
if (!$conn) {
    // die() 會終止程式執行，並顯示錯誤訊息
    die("資料庫連接失敗: " . mysqli_connect_error());
}

// 設定連線編碼為 utf8mb4
// 這非常重要，確保中文資料在存取時不會變成亂碼
mysqli_set_charset($conn, "utf8mb4");
?>