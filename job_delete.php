<?php
// job_delete.php
// --------------------------------------------------------
// 用途：刪除職缺/活動功能頁面
// 功能：
// 1. 顯示要刪除的資料以供確認
// 2. 執行刪除動作 (需驗證權限)
// --------------------------------------------------------

// 引入頁首 (包含 session_start 和導覽列)
include "header.php";

// 再次檢查 session 狀態 (雖然 header.php 有做，但多做無害)
if (session_status() === PHP_SESSION_NONE)
  session_start();

// 初始化變數
$postid = $company = $content = $pdate = "";

// 使用 try-catch 捕捉可能發生的錯誤
try {
  // 檢查是否有 GET 參數 (從網址列傳來)
  if ($_GET) {
    // 引入資料庫連線
    require_once 'db.php';

    // 取得動作參數，預設為空字串
    $action = $_GET["action"] ?? "";

    // 情境 1: 真正執行刪除 ($action 為 confirmed)
    if ($action == "confirmed") {

      // 權限檢查：只有管理員 (Role='M') 才能執行刪除
      if (!empty($_SESSION['role']) && strtoupper(trim($_SESSION['role'])) === 'M') {
        // 取得要刪除的職缺 ID
        $postid = $_GET["postid"];

        // 準備 SQL 刪除語句
        $sql = "DELETE FROM job WHERE postid=?";

        // 使用 Prepared Statement 防止 SQL Injection
        $stmt = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt, $sql);

        // 綁定參數 (i 代表整數)
        mysqli_stmt_bind_param($stmt, "i", $postid);

        // 執行刪除
        mysqli_stmt_execute($stmt);

        // 關閉連線
        mysqli_close($conn);

        // 刪除完成後，導回首頁列表
        header('Location: index.php');
        exit;
      } else {
        // 權限不足的錯誤處理
        echo "<div class='container mt-3'>
                <div class='alert alert-danger'>只有管理員可以刪除職缺</div>
                <a href='index.php' class='btn btn-secondary'>返回列表</a>
              </div>";
        exit;
      }
    } else {
      // 情境 2: 顯示確認畫面 ($action 不為 confirmed)
      // 根據 ID 查詢資料庫，顯示詳細資料讓使用者確認
      $postid = $_GET["postid"];

      // SQL 查詢語句
      $sql = "SELECT postid, company, content, pdate FROM job WHERE postid=?";

      // 準備與執行查詢
      $stmt = mysqli_stmt_init($conn);
      mysqli_stmt_prepare($stmt, $sql);
      mysqli_stmt_bind_param($stmt, "i", $postid);
      mysqli_stmt_execute($stmt);

      // 將結果綁定到變數
      mysqli_stmt_bind_result($stmt, $postid, $company, $content, $pdate);

      // 取得資料
      mysqli_stmt_fetch($stmt);

      // 關閉連線 (注意：這裡關閉後，下方 HTML 就不能再查詢 DB 了)
      mysqli_close($conn);
    }
  }
} catch (Exception $e) {
  // 捕捉並顯示例外錯誤訊息
  echo 'Message: ' . $e->getMessage();
}
?>

<!-- HTML 內容開始 -->
<div class="container">
  <!-- 顯示資料表格 -->
  <table class="table table-bordered table-striped">
    <tr>
      <td>編號</td>
      <td>求才廠商</td>
      <td>求才內容</td>
      <td>刊登日期</td>
    </tr>
    <tr>
      <!-- 顯示查詢到的資料 -->
      <td><?= $postid ?></td>
      <td><?= $company ?></td>
      <td><?= $content ?></td>
      <td><?= $pdate ?></td>
    </tr>
  </table>

  <!-- 根據權限顯示對應按鈕 -->
  <?php if (!empty($_SESSION['role']) && strtoupper(trim($_SESSION['role'])) === 'M'): ?>
    <!-- 若是管理員，顯示紅色的刪除確認按鈕 (連結包含 action=confirmed) -->
    <a href="job_delete.php?postid=<?= $postid ?>&action=confirmed" class="btn btn-danger">刪除</a>
  <?php else: ?>
    <!-- 若非管理員，顯示警告並提供返回按鈕 -->
    <div class="alert alert-danger">只有管理員可以刪除職缺</div>
    <a href="index.php" class="btn btn-secondary">返回列表</a>
  <?php endif; ?>
</div>

<?php
// 引入頁尾
require_once "footer.php";
?>