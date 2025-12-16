<?php
// nu_about.php
// --------------------------------------------------------
// 用途：迎新茶會的報名頁面 (Frontend)
// --------------------------------------------------------

// 引入頁首 (session_start() 已在其中執行)
include "header.php";

// 權限檢查機制
// 判斷使用者是否已登入 (檢查 Session)
if (!isset($_SESSION["account"])) {
  // 取得當前網址路徑，並進行 URL 編碼
  // 這樣登入後可以跳轉回原本想訪問的頁面
  $redirect = urlencode($_SERVER["REQUEST_URI"]);

  // 導向至登入頁
  header("Location: login.php?redirect=$redirect");
  exit; // 終止程式
}

// 讀取已經存在 Session 中的使用者資料
// 因為在 login.php 登入成功時已經放入了
$name = $_SESSION["name"]; // 姓名
$role = $_SESSION["role"]; // 身分 (S=學生, M=老師/管理員等)
?>

<div class="container my-5">

  <!-- 建立表單，送出到 nu_c_about.php (c 可能代表 calculate 計算) -->
  <!-- target="_blank" 代表提交後會開啟新分頁顯示結果 -->
  <form action="nu_c_about.php" method="post" target="_blank">

    <!-- 隱藏欄位 (Hidden Input) -->
    <!-- 使用者看不見，但會隨著表單送出 -->
    <!-- 這裡將 Session 中的姓名與身分再次傳給後端計算頁面 -->
    <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
    <input type="hidden" name="status" value="<?= $role ?>">

    <!-- 晚餐需求選項 (Radio Button 單選) -->
    <div class="mb-3">
      <label class="form-label">晚餐需求:</label><br>
      <!-- name="dinner" 必須相同才能互斥 (多選一) -->
      <!-- checked 代表預設選取此項 -->
      <input type="radio" name="dinner" value="yes" checked> 需要晚餐
      <input type="radio" name="dinner" value="no"> 不需要晚餐
    </div>

    <!-- 送出按鈕 -->
    <button type="submit" class="btn btn-primary">送出</button>
  </form>
</div>

<?php
// 引入頁尾
include "footer.php";
?>