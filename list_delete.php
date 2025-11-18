<?php
include "header.php";


if (session_status() === PHP_SESSION_NONE) session_start();

$postid = $company = $content = $pdate = "";

try {
    if ($_GET) {
        require_once 'db.php';
        $action = $_GET["action"] ?? "";

        if ($action == "confirmed") {
            // 管理員才能刪
            if (!empty($_SESSION['role']) && strtoupper(trim($_SESSION['role'])) === 'M') {
                $postid = $_GET["postid"];
                $sql = "DELETE FROM job WHERE postid=?";
                $stmt = mysqli_stmt_init($conn);
                mysqli_stmt_prepare($stmt, $sql);
                mysqli_stmt_bind_param($stmt, "i", $postid);
                mysqli_stmt_execute($stmt);
                mysqli_close($conn);
                header('Location: job.php');
                exit;
            } else {
                echo "<div class='container mt-3'>
                        <div class='alert alert-danger'>只有管理員可以刪除職缺</div>
                        <a href='job.php' class='btn btn-secondary'>返回列表</a>
                      </div>";
                exit;
            }
        } else {
            // 顯示資料
            $postid = $_GET["postid"];
            $sql = "SELECT postid, company, content, pdate FROM job WHERE postid=?";
            $stmt = mysqli_stmt_init($conn);
            mysqli_stmt_prepare($stmt, $sql);
            mysqli_stmt_bind_param($stmt, "i", $postid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $postid, $company, $content, $pdate);
            mysqli_stmt_fetch($stmt);
            mysqli_close($conn);
        }
    }
} catch(Exception $e) {
    echo 'Message: ' .$e->getMessage();
}
?>

<div class="container">
  <table class="table table-bordered table-striped">
    <tr>
      <td>編號</td>
      <td>主辦單位</td>
      <td>活動內容</td>
      <td>刊登日期</td>
    </tr>
    <tr>
      <td><?=$postid?></td>
      <td><?=$company?></td>
      <td><?=$content?></td>
      <td><?=$pdate?></td>
    </tr>
  </table>

  <?php if (!empty($_SESSION['role']) && strtoupper(trim($_SESSION['role'])) === 'M'): ?>
      <a href="job_delete.php?postid=<?=$postid?>&action=confirmed" class="btn btn-danger">刪除</a>
  <?php else: ?>
      <div class="alert alert-danger">只有管理員可以刪除職缺</div>
      <a href="job.php" class="btn btn-secondary">返回列表</a>
  <?php endif; ?>
</div>

<?php require_once "footer.php"; ?>
