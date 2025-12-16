<?php
// header.php
// --------------------------------------------------------
// 用途：網站頁首與導覽列元件
// 功能：
// 1. 啟動或延續 Session
// 2. 判斷使用者狀態 (登入/未登入)
// 3. 輸出 HTML head 區域 (包含 Bootstrap CSS)
// 4. 輸出導覽列 (Navbar)
// --------------------------------------------------------

// 檢查 Session 狀態，若尚未啟動則啟動它
// 這是極為重要的步驟，因為幾乎所有頁面都需要讀取 $_SESSION 變數
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// 取得目前的頁面路徑 (例如: /web5_final_test/index.php)
// 這通常用於登入後導回原頁面 (已註解掉的用法)
$current_page = $_SERVER["REQUEST_URI"];

// 定義一個輔助函式 nav_active($file)
// 用途：判斷導覽列項目是否為「當前頁面」，如果是就加上 'active' 樣式 Class
function nav_active($file)
{
  // 取得當前執行的腳本名稱 (不含路徑)
  $current = basename($_SERVER['PHP_SELF']);
  // 如果傳入的檔名與當前檔名相同，回傳 ' active' 字串
  // Bootstrap 會將有 active class 的 nav-link 視為選中狀態 (高亮顯示)
  return $current === $file ? ' active' : '';
}

// 判斷使用者是否已登入
// 依據 Session 中是否有 'account' 變數來決定
if (isset($_SESSION["account"])) {
  // 若已登入
  // 設定按鈕連結到 logout.php
  $login_url = "logout.php";
  // 按鈕文字顯示「登出」
  $login_text = "登出";
} else {
  // 若未登入
  // 設定按鈕連結到 login.php，並帶上 redirect 參數以便登入後跳回本頁
  $login_url = "login.php?redirect=" . urlencode($current_page);
  // 按鈕文字顯示「登入」
  $login_text = "登入";
}
?>
<!-- HTML Head 區域開始 -->

<head>
  <!-- 設定網頁編碼為 UTF-8 (支援多國語言) -->
  <meta charset="UTF-8">
  <!-- 設定視口，確保在行動裝置上能正確縮放 (RWD 響應式設計基礎) -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- 網頁標題 -->
  <title>高中生營隊活動系統</title>
  <!-- 引入 Bootstrap 5.3.2 CSS 框架 (CDN 連結) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <!-- body 設為 Flexbox 佈局，min-vh-100 確保高度至少撐滿視窗，bg-light 設定淺灰背景 -->

  <body class="d-flex flex-column min-vh-100 bg-light">

    <!-- 導覽列 (Navbar) 開始 -->
    <!-- navbar-expand-lg: 大螢幕展開，小螢幕摺疊; navbar-dark bg-dark: 深色主題 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container-fluid">
        <!-- 網站品牌標誌/名稱 (點擊回首頁) -->
        <a class="navbar-brand" href="index.php">高中生營隊活動系統</a>

        <!-- 手機版漢堡選單按鈕 (當螢幕變窄時出現) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- 導覽列連結內容區塊 (可折疊區域) -->
        <div class="collapse navbar-collapse" id="navbarNav">
          <!-- navbar-nav ms-auto: 將選單項目推到最右邊 (margin-start: auto) -->
          <ul class="navbar-nav ms-auto">

            <!-- 首頁連結 -->
            <li class="nav-item">
              <!-- 呼叫 nav_active() 檢查是否為當前頁面 -->
              <a class="nav-link<?= nav_active('index.php') ?>" href="index.php">首頁</a>
            </li>

            <!-- 我的報名連結 -->
            <li class="nav-item">
              <a class="nav-link<?= nav_active('my_applications.php') ?>" href="my_applications.php">我的報名</a>
            </li>

            <!-- 通知連結 -->
            <li class="nav-item">
              <a class="nav-link<?= nav_active('my_notifications.php') ?>" href="my_notifications.php">通知</a>
            </li>

            <!-- 個人資料連結 -->
            <li class="nav-item">
              <a class="nav-link<?= nav_active('personal.php') ?>" href="personal.php">個人資料</a>
            </li>

            <!-- 登入/登出按鈕 (動態變數) -->
            <li class="nav-item">
              <a class="nav-link" href="<?= $login_url ?>"><?= $login_text ?></a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- 導覽列結束 -->