<?php
// db.php
// XAMPP 的資料庫連接設定
// 連接到您 'practice (3).sql' 檔案中指定的資料庫 'practice'

$servername = "localhost"; // XAMPP 預設是 localhost
$username = "root";        // XAMPP 預設是 root
$password = "";            // XAMPP 預設密碼為空
$dbname = "practice";      // 這是您 .sql 檔案中指定的資料庫名稱

// 建立連接
$conn = mysqli_connect($servername, $username, $password, $dbname);

// 檢查連接
if (!$conn) {
    die("連接失敗: " . mysqli_connect_error());
}

// 設定連線編碼為 utf8mb4
mysqli_set_charset($conn, "utf8mb4");

?>