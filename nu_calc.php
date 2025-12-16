<?php
// nu_calc.php
// --------------------------------------------------------
// 用途：資管一日營費用計算 (Backend)
// --------------------------------------------------------

// 啟動 session 以獲取使用者資訊
session_start();

// 權限檢查：若未登入則導回 nu_contact.php
// 雖然理論上 nu_contact.php 也會檢查，但後端需再次確認確保安全
if (!isset($_SESSION["account"])) {
    header("Location: nu_contact.php");
    exit; // 終止腳本
}

// 檢查是否有 POST 表單送出
// 如果沒有 POST 資料，代表使用者可能直接輸入網址，將其導回填表頁
if (!$_POST) {
    header("Location: nu_contact.php");
    exit;
}

// 設定各項活動的價格表 (Array)
// 索引 0: 佔位符 (無實際活動) -> $0
// 索引 1: 上午場 -> $150
// 索引 2: 下午場 -> $100
// 索引 3: 午餐   -> $60
$program_price = array(0, 150, 100, 60);

// 從 Session 取得使用者姓名與身分
// 若無則給予預設值 (雖然登入後通常會有)
$name = $_SESSION["name"] ?? "N/A";
$status = $_SESSION["role"] ?? "S";

// 取得使用者勾選的活動清單 (Checkbox 陣列)
// 若使用者什麼都沒勾，預設為陣列 [0] (佔位用)
$programlist = $_POST["program"] ?? [0];

// 初始化總金額
$price = 0;

// 遍歷所有勾選的項目
foreach ($programlist as $program) {
    // 累加對應項目的價格
    // $program 若為 1，則 $price += $program_price[1] (150)
    $price += $program_price[$program];
}

// 特殊優惠邏輯：老師免費
// 檢查 Session 中的 role 是否為 'T' (Teacher)
if ($status === 'T') {
    $price = 0; // 金額歸零
}

// 輸出最終結果
// 顯示：姓名（身分），你要繳交 X 元
echo htmlspecialchars($name) . "（" . htmlspecialchars($status) . "），你要繳交 " . $price . " 元";
?>