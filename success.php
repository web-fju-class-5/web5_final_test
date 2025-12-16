<?php
// success.php
// --------------------------------------------------------
// 用途：登入成功頁面 (過渡頁)
// 功能：
// 1. 驗證 Session 確保已登入
// 2. 顯示歡迎訊息
// 3. 提供前往其他功能的入口
// --------------------------------------------------------

// 1. 啟動 Session
session_start();

// 2. 安全性檢查：若未登入則導回登入頁
// 檢查 $_SESSION["account"] 是否存在
if (!isset($_SESSION["account"])) {
	// 未登入，轉址到 login.php
	header("Location: login.php");
	exit; // 終止腳本
}
?>

<html lang="zh-Hant">

<head>
	<!-- 設定字元編碼 -->
	<meta charset="utf-8">
	<!-- 設定 RWDviewport -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>登入成功</title>
	<!-- 引入 Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
	<!-- 主要容器 -->
	<div class="container mt-5">
		<div class="row justify-content-center">
			<div class="col-md-6"> <!-- 置中且寬度佔半 -->
				<div class="card shadow"> <!-- 卡片元件帶陰影 -->
					<div class="card-body text-center">

						<!-- 顯示歡迎訊息 (從 Session 取出帳號) -->
						<h3 class="mb-4">歡迎，<?= htmlspecialchars($_SESSION["account"]) ?></h3>

						<!-- 按鈕群組 -->
						<!-- 回登入頁 (其實就是登出，因為 login.php 會檢查狀態或重新登入) -->
						<a href="login.php" class="btn btn-outline-primary me-2">回登入頁</a>

						<!-- 進入第二功能頁 (範例連結) -->
						<a href="success2.php" class="btn btn-primary">進入 success2.php</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- 引入 Bootstrap JS Bundle -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>