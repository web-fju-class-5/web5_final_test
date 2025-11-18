<?php
include "header.php";

// ✅ 確保 session 已啟動
if (session_status() === PHP_SESSION_NONE) session_start();

$id = $name = $description  = "";

try {
    require_once 'db.php';

    $action = $_GET["action"] ?? "";
    $id = $_GET["postid"] ?? null; // 使用 postid 參數

    if (!$id) {
        echo "<div class='container mt-3'>
                <div class='alert alert-danger'>找不到活動ID</div>
                <a href='activity.php' class='btn btn-secondary'>返回列表</a>
              </div>";
        exit;
    }

    if ($action == "confirmed") {
        // 管理員才能刪除
        if (!empty($_SESSION['role']) && strtoupper(trim($_SESSION['role'])) === 'M') {
            $sql = "DELETE FROM event WHERE id=?";
            $stmt = mysqli_stmt_init($conn);
            mysqli_stmt_prepare($stmt, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_close($conn);
            header('Location: index.php');
            exit;
        } else {
            echo "<div class='container mt-3'>
                    <div class='alert alert-danger'>只有管理員可以刪除活動</div>
                    <a href='activity.php' class='btn btn-secondary'>返回列表</a>
                  </div>";
            exit;
        }
    } else {
        // 顯示資料
        $sql = "SELECT id, name, description FROM event WHERE id=?";
        $stmt = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id, $name, $description);
        mysqli_stmt_fetch($stmt);
        mysqli_close($conn);
    }

} catch(Exception $e) {
    echo 'Message: ' .$e->getMessage();
}
?>

<div class="container">
  <table class="table table-bordered table-striped">
    <tr>
      <td>活動名稱</td>
      <td>活動內容</td>
    </tr>
    <tr>
      <td><?=htmlspecialchars($name)?></td>
      <td><?=nl2br(htmlspecialchars($description))?></td>
    </tr>
  </table>

  <?php if (!empty($_SESSION['role']) && strtoupper(trim($_SESSION['role'])) === 'M'): ?>
      <a href="activity_delete.php?postid=<?=$id?>&action=confirmed" class="btn btn-danger">刪除</a>
  <?php else: ?>
      <div class="alert alert-danger">只有管理員可以刪除活動</div>
      <a href="activity.php" class="btn btn-secondary">返回列表</a>
  <?php endif; ?>
</div>

<?php require_once "footer.php"; ?>
