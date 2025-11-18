<?php
require_once "header.php";
require_once "db.php";

// 檢查是否有 POST 表單送出
if ($_POST) {
    $name = $_POST["company"] ?? "";
    $description = $_POST["content"] ?? "";

    // 避免 SQL Injection
    $name_safe = mysqli_real_escape_string($conn, $name);
    $description_safe = mysqli_real_escape_string($conn, $description);

    $sql = "INSERT INTO event (name, description ) VALUES (?, ?)";
    $stmt = mysqli_stmt_init($conn);
    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $name_safe, $description_safe);
        if (mysqli_stmt_execute($stmt)) {
            // 新增成功跳回列表頁
            header("Location: index.php");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>新增失敗</div>";
        }
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<div class="container my-5">
<form action="activity_insert.php" method="post">
  <div class="mb-3 row">
    <label for="_company" class="col-sm-2 col-form-label">活動名稱</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="company" id="_company" placeholder="活動名稱" required>
    </div>
  </div>
  <div class="mb-3">
    <label for="_content" class="form-label">活動內容</label>
    <textarea class="form-control" name="content" id="_content" rows="10" required></textarea>
  </div>
  <input class="btn btn-primary" type="submit" value="送出">
</form>

</div>

<?php
include "footer.php";
?>
