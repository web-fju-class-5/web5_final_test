<?php
//登入成功畫面
session_start();
if (!isset($_SESSION["account"])) {
	header("Location: login.php");
	exit;
}
?>

<html lang="zh-Hant">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>登入成功</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
	<div class="row justify-content-center">
		<div class="col-md-6">
			<div class="card shadow">
				<div class="card-body text-center">
					<h3 class="mb-4">歡迎，<?=htmlspecialchars($_SESSION["account"])?></h3>
					<a href="login.php" class="btn btn-outline-primary me-2">回登入頁</a>
					<a href="success2.php" class="btn btn-primary">進入 success2.php</a>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>