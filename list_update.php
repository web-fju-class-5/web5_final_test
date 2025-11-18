<?php
require_once "header.php";
require_once "db.php";


// 只有管理員可以修改
if (empty($_SESSION['role']) || strtoupper(trim($_SESSION['role'])) !== 'M') {
    die("只有管理員可以修改資料");
}

// 取得 postid
$postid = $_GET['postid'] ?? $_POST['postid'] ?? null;
if (!$postid) {
    die("缺少資料ID");
}

$msg = "";

// 處理 POST 更新資料
if ($_POST) {
    $company = $_POST["company"] ?? "";
    $content = $_POST["content"] ?? "";

    // 避免 SQL Injection
    $company_safe = mysqli_real_escape_string($conn, $company);
    $content_safe = mysqli_real_escape_string($conn, $content);

    $sql = "UPDATE job SET company = ?, content = ? WHERE postid = ?";
    $stmt = mysqli_stmt_init($conn);
    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $company_safe, $content_safe, $postid);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header("Location: job.php");
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>更新失敗</div>";
        }
    }
}

// GET 請求：撈出原本資料
else {
    $stmt = mysqli_stmt_init($conn);
    $sql = "SELECT company, content FROM job WHERE postid = ?";
    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $postid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $company, $content);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<div class="container my-5">
<form action="job_update.php?postid=<?=$postid?>&action=confirmed" method="post">

  <!-- 隱藏欄位傳送 postid -->
  <input type="hidden" name="postid" value="<?=$postid?>">

  <div class="mb-3 row">
    <label for="_company" class="col-sm-2 col-form-label">主辦單位</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="company" id="_company" 
             placeholder="公司名稱" value="<?=htmlspecialchars($company)?>" required>
    </div>
  </div>
  <div class="mb-3">
    <label for="_content" class="form-label">活動內容</label>
    <textarea class="form-control" name="content" id="_content" rows="10" required><?=htmlspecialchars($content)?></textarea>
  </div>
  <input class="btn btn-primary" type="submit" value="送出">
</form>
<?=$msg?>
</div>

<?php
require_once "footer.php";
?>
