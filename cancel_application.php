<?php
// cancel_application.php
// --------------------------------------------------------
// 後端處理程式：
// 負責從 applications 資料表中刪除一筆報名紀錄。
// --------------------------------------------------------

session_start();
require_once 'db.php';

// 1. 安全檢查
if (!isset($_SESSION['account'])) {
    die("請先登入。");
}

// 2. 獲取要刪除的 ID (applications.id)
$app_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$account = $_SESSION['account'];

if ($app_id > 0) {
    // 3. 執行刪除 SQL
    // 重要安全機制：務必加上 AND user_account = '$account'
    // 防止使用者透過修改網址參數去刪除「別人」的報名紀錄
    $sql = "DELETE FROM applications WHERE id = $app_id AND user_account = '$account'";
    
    if (mysqli_query($conn, $sql)) {
        // 檢查影響行數 (是否真的有刪除到資料)
        if (mysqli_affected_rows($conn) > 0) {
            echo "<script>
                    alert('已成功取消報名。');
                    window.location.href = 'my_applications.php';
                  </script>";
        } else {
            echo "<script>
                    alert('刪除失敗：找不到資料或無權限。');
                    window.location.href = 'my_applications.php';
                  </script>";
        }
    } else {
        echo "SQL 錯誤: " . mysqli_error($conn);
    }
} else {
    echo "<script>
            alert('無效的操作。');
            window.location.href = 'my_applications.php';
          </script>";
}

mysqli_close($conn);
?>