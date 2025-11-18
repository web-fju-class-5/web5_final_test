<?php
include "header.php";
include "db.php";

// 1️⃣ 檢查是否有 POST 表單送出
if ($_POST) {
    $company = $_POST["company"] ?? "";
    $content = $_POST["content"] ?? "";

    // 避免 SQL Injection
    $company_safe = mysqli_real_escape_string($conn, $company);
    $content_safe = mysqli_real_escape_string($conn, $content);

    $sql = "INSERT INTO job (company, content, pdate) VALUES (?, ?, NOW())";
    $stmt = mysqli_stmt_init($conn);
    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $company_safe, $content_safe);
        if (mysqli_stmt_execute($stmt)) {
            // ✅ 新增成功直接跳轉回 job.php
            header("Location: job.php");
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
<form action="job_insert.php" method="post">
  <div class="mb-3 row">
    <label for="_company" class="col-sm-2 col-form-label">求才廠商</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="company" id="_company" placeholder="公司名稱" required>
    </div>
  </div>
  <div class="mb-3">
    <label for="_content" class="form-label">求才內容</label>
    <textarea class="form-control" name="content" id="_content" rows="10" required></textarea>
  </div>
  <input class="btn btn-primary" type="submit" value="送出">
</form>

</div>

<?php
require_once "footer.php";
?>
