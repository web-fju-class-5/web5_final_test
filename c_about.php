<?php
//茶會算錢





// 如果沒有表單送出，導回 status.html
if (!$_POST) {
    header("Location:about.php ");
    exit;
}

// 取得姓名
$name = $_POST["name"] ?? "N/A";

// 取得身份
$status = $_POST["status"] ?? "S"; // 預設學生

// 預設= 0
$price = 0;

// 老師免費
if ($status === "S" || $status === "M" ) {
    // 學生且勾選需要用餐才收費 60 元
    if (isset($_POST["dinner"]) && $_POST["dinner"] === "yes") {
        $price = 60;
    }
}

// 顯示結果
echo htmlspecialchars($name) . "，你要繳交 " . $price . " 元";
