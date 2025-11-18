<?php
include "header.php";

// 權限檢查
if (!isset($_SESSION["account"])) {
    $redirect = urlencode($_SERVER["REQUEST_URI"]);
    header("Location: login.php?redirect=$redirect");
    exit;
}

// 從 session 取得登入者姓名與身分
$name = $_SESSION["name"];
$role = $_SESSION["role"];
?>

<div class="container my-5">
  <h2>資管一日營報名</h2>

  <form action="calc.php" method="post" target="_blank">

    <!-- ✅ 隱藏姓名與身分 -->
    <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
    <input type="hidden" name="status" value="<?= $role ?>">

    <!-- 活動/餐點選項 -->
    <div class="mb-3">
      <label class="form-label">活動/餐點:</label><br>
      <input type="checkbox" name="program[]" value="1"> 上午場 ($150)
      <input type="checkbox" name="program[]" value="2"> 下午場 ($100)
      <input type="checkbox" name="program[]" value="3"> 午餐 ($60)
    </div>

    <button type="submit" class="btn btn-primary">送出</button>
  </form>
</div>

<?php
include "footer.php";
?>
