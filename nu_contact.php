<?php
// nu_contact.php
// --------------------------------------------------------
// 用途：資管一日營的報名表單頁面
// --------------------------------------------------------

// 引入頁首 (session_start() 已執行)
include "header.php";

// 權限檢查：確保已登入
if (!isset($_SESSION["account"])) {
  // 取得當前網址，以便登入後導回
  $redirect = urlencode($_SERVER["REQUEST_URI"]);
  // 導向 login.php
  header("Location: login.php?redirect=$redirect");
  exit;
}

// 從 Session 讀取目前使用者的姓名與權限
$name = $_SESSION["name"];
$role = $_SESSION["role"];
?>

<div class="container my-5">
  <h2>資管一日營報名</h2>

  <!-- 表單設定 -->
  <!-- action: 送出到 nu_calc.php 進行費用計算 -->
  <!-- method: POST -->
  <!-- target="_blank": 開啟新分頁顯示計算結果 -->
  <form action="nu_calc.php" method="post" target="_blank">

    <!-- 隱藏欄位 (Hidden Input) -->
    <!-- 傳送姓名與身分給後端 nu_calc.php (雖然 session 也有，但維持跟 nu_about.php 一致的做法) -->
    <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
    <input type="hidden" name="status" value="<?= $role ?>">

    <!-- 活動與餐點選項 (核取方塊 Checkbox) -->
    <!-- name="program[]": 使用陣列後綴 [] 代表可多選，後端會收到陣列 -->
    <div class="mb-3">
      <label class="form-label">活動/餐點:</label><br>

      <!-- 選項 1: 上午場 -->
      <input type="checkbox" name="program[]" value="1"> 上午場 ($150)

      <!-- 選項 2: 下午場 -->
      <input type="checkbox" name="program[]" value="2"> 下午場 ($100)

      <!-- 選項 3: 午餐 -->
      <input type="checkbox" name="program[]" value="3"> 午餐 ($60)
    </div>

    <!-- 送出按鈕 -->
    <button type="submit" class="btn btn-primary">送出</button>
  </form>
</div>

<?php
// 引入頁尾
include "footer.php";
?>