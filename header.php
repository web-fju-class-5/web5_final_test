<?php
// header.php - FINAL VERSION
// Feature: Navigation Bar + Avatar Display
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php'; 

$current_page = $_SERVER["REQUEST_URI"];

function nav_active($file) {
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $file ? ' active' : '';
}

$avatar_html = ""; 

// Check Login Status
if (isset($_SESSION["account"])) {
    $login_url = "logout.php";
    $login_text = "登出";
    
    // --- FETCH AVATAR ---
    $acc = $_SESSION['account'];
    $sql_av = "SELECT avatar FROM user WHERE account = '$acc'";
    $res_av = mysqli_query($conn, $sql_av);
    $row_av = mysqli_fetch_assoc($res_av);
    
    $img_src = !empty($row_av['avatar']) ? $row_av['avatar'] : "https://via.placeholder.com/30";
    
    $avatar_html = "<img src='$img_src' class='rounded-circle border' 
                         style='width: 30px; height: 30px; object-fit: cover; margin-right: 8px;'>";
    
} else {
    $login_url = "login.php?redirect=" . urlencode($current_page);
    $login_text = "登入";
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>高中生營隊活動系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">高中生營隊活動系統</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item">
            <a class="nav-link<?= nav_active('index.php') ?>" href="index.php">首頁</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?= nav_active('my_applications.php') ?>" href="my_applications.php">我的報名</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?= nav_active('my_notifications.php') ?>" href="my_notifications.php">通知</a>
          </li>
          <li class="nav-item">
            <a class="nav-link<?= nav_active('personal.php') ?>" href="personal.php">個人資料</a>
          </li>
          <li class="nav-item mx-2"></li>
          <li class="nav-item d-flex align-items-center">
             <?php if(isset($_SESSION["account"])): ?>
                <a href="personal.php" title="更換頭像">
                    <?= $avatar_html ?>
                </a>
             <?php endif; ?>
             <a class="btn btn-outline-light btn-sm" href="<?= $login_url ?>"><?= $login_text ?></a>
          </li>
        </ul>
      </div>
    </div>
  </nav>