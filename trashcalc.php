<?php
// 資管一日營算錢

session_start();

// 如果沒登入，導回 contact.php
if (!isset($_SESSION["account"])) {
    header("Location: contact.php");
    exit;
}

// 如果沒有 POST 送過來就導回表單頁
if (!$_POST) {
    header("Location: contact.php");
    exit;
}

// 設定活動價格
$program_price = array(0, 150, 100, 60);

$name = $_SESSION["name"] ?? "N/A";
$status = $_SESSION["role"] ?? "S";

// 取得活動選項
$programlist = $_POST["program"] ?? [0];

//算錢
$price = 0;
foreach ($programlist as $program) {
    $price += $program_price[$program];
}

// 老師免費
if ($status === 'T') {
    $price = 0;
}

// 顯示結果
echo htmlspecialchars($name) . "（" . htmlspecialchars($status) . "），你要繳交 " . $price . " 元";
?>
