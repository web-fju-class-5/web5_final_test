<?php
// about.php (迎新茶會)
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

  <form action="c_about.php" method="post" target="_blank">

    <!-- ✅ 隱藏姓名與身分，仍會送到後端 -->
    <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
    <input type="hidden" name="status" value="<?= $role ?>">

    <!-- ✅ 晚餐需求選項 -->
    <div class="mb-3">
      <label class="form-label">晚餐需求:</label><br>
      <input type="radio" name="dinner" value="yes" checked> 需要晚餐
      <input type="radio" name="dinner" value="no"> 不需要晚餐
    </div>

    <button type="submit" class="btn btn-primary">送出</button>
  </form>
</div>

<?php
include "footer.php";
?>
