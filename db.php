<?php
// db.php
// --------------------------------------------------------
// 此檔案負責建立與 MySQL 資料庫的連線。
// 在其他頁面使用 require_once 'db.php'; 即可使用 $conn 變數。
// --------------------------------------------------------

// 資料庫主機，XAMPP 預設為 localhost
$servername = "localhost"; 

// 資料庫使用者名稱，XAMPP 預設為 root
$username = "root";        

// 資料庫密碼，XAMPP 預設為空字串
$password = "";            

// 要連接的資料庫名稱，需對應 SQL 檔中的資料庫名
$dbname = "practice";      

// 1. 建立連線物件
$conn = mysqli_connect($servername, $username, $password, $dbname);

// 2. 檢查連線是否成功
if (!$conn) {
    // 若失敗，輸出錯誤訊息並終止程式執行
    die("資料庫連接失敗: " . mysqli_connect_error());
}

// 3. 設定連線編碼為 utf8mb4
// 這是為了確保中文資料在存取時不會出現亂碼
mysqli_set_charset($conn, "utf8mb4");
?>