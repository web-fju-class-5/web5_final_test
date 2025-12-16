<?php
// nu_c_about.php
// --------------------------------------------------------
// 用途：接收迎新茶會報名資料，計算並顯示費用
// --------------------------------------------------------

// 這個頁面是單純的結果處理頁，不需要頁首頁尾 (依據原程式碼風格)

// 檢查是否有 POST 資料送出
// 若直接輸入網址訪問此頁 (沒經過表單)，則將其導回 nu_about.php
if (!$_POST) {
    header("Location:nu_about.php");
    exit;
}

// 1. 取得 POST 傳過來的姓名
// 若沒傳送則預設為 "N/A"
$name = $_POST["name"] ?? "N/A";

// 2. 取得 POST 傳過來的身分
// 若沒傳送則預設為 "S" (學生)
$status = $_POST["status"] ?? "S";

// 3. 初始化費用變數
$price = 0;

// 4. 費用計算邏輯
// 若身分是 'S' (Student) 或 'M' (Manager/Member?)
// 這裡假設除了老師以外都要錢? 或者 M 也是要付錢的角色
if ($status === "S" || $status === "M") {
    // 檢查晚餐選項
    // 只有在勾選 "yes" 時才需要付費
    if (isset($_POST["dinner"]) && $_POST["dinner"] === "yes") {
        $price = 60; // 晚餐費用 60 元
    }
}
// 若 condition 不成立 (例如 status 為 'T' 老師)，則維持 $price = 0

// 5. 輸出結果
// 使用 htmlspecialchars 防止 XSS 攻擊
echo htmlspecialchars($name) . "，你要繳交 " . $price . " 元";
?>