<?php
// db.php
// --------------------------------------------------------
// 此檔案負責建立與 MySQL 資料庫的連線。
// 在其他頁面使用 require_once 'db.php'; 即可使用 $conn 變數。
// --------------------------------------------------------

// 設定資料庫連線參數
// $servername: 資料庫主機位置，本機開發通常使用 localhost
// 注意：XAMPP 有時 MySQL port 會是 3306 或 3307，請依實際狀況調整
$servername = "localhost:3307";

// $username: 資料庫使用者名稱
// XAMPP 預設管理員帳號為 "root"
$username = "root";

// $password: 資料庫密碼
// XAMPP 預設 root 帳號密碼為空字串
$password = "";

// $dbname: 目標資料庫名稱
// 必須先在 phpMyAdmin 或透過 SQL 腳本建立此資料庫 'practice'
$dbname = "practice";

// 1. 建立資料庫連線物件
// mysqli_connect() 函式嘗試開啟一個到 MySQL 伺服器的連線
// 參數依序為：主機、帳號、密碼、資料庫名稱
$conn = mysqli_connect($servername, $username, $password, $dbname);

// 2. 檢查連線是否成功
// 如果 $conn 為 false，代表連線失敗
if (!$conn) {
    // die() 函式會輸出訊息並立即終止 PHP 程式執行
    // mysqli_connect_error() 會回傳上一次連線錯誤的詳細訊息
    die("資料庫連接失敗: " . mysqli_connect_error());
}

// 3. 設定連線編碼
// mysqli_set_charset() 設定客戶端與資料庫之間傳輸字元的編碼
// 使用 "utf8mb4" 是為了完整支援 Unicode (包含 Emoji 表情符號與繁體中文)
mysqli_set_charset($conn, "utf8mb4");

// 程式執行到此若無錯誤，表示 $conn 已準備好可供查詢使用
?>