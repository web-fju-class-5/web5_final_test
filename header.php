<?php
// header.php
//抬頭選單

// 開始 session，如果還沒開始
if (session_status() === PHP_SESSION_NONE) {
    session_start(); //
}




// 取得目前頁面
$current_page = $_SERVER["REQUEST_URI"]; // ✅ 修改：取得當前頁面 URL，用於 redirect

function nav_active($file) {
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $file ? ' active' : '';
}

// 判斷登入狀態，決定登入/登出按鈕
if (isset($_SESSION["account"])) {
    $login_url = "logout.php"; // 已登入就顯示登出
    $login_text = "登出";
} else {
    $login_url = "login.php?redirect=" . urlencode($current_page); // 未登入就顯示登入，帶回原頁面
    $login_text = "登入";
}
?>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>高中生營隊活動系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <body class="d-flex flex-column min-vh-100 bg-light">

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">高中生營隊活動系統</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <!--
            <a class="nav-link<?= nav_active('index.php') ?>" href="index.php">首頁</a>
          </li>
-->
          <li class="nav-item">
            <a class="nav-link<?= nav_active('index.php') ?>" href="index.php">活動列表</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?= nav_active('activity.php') ?>" href="activity.php">報名去</a>
          <li class="nav-item">
            <a class="nav-link<?= nav_active('notify.php') ?>" href="notify.php">通知</a>
            <!--  
          
          <li class="nav-item">
              -->
          <!--
            <a class="nav-link<?= nav_active('personal.php') ?>" href="personal.php">個人資料</a>
          </li>
          -->
          
          <li class="nav-item">
            <a class="nav-link" href="<?= $login_url ?>"><?= $login_text ?></a> <!-- ✅ 修改：登入/登出切換 -->
          </li>
        </ul>
      </div>
    </div>
  </nav>
