<?php
// cancel_application.php
// 後端處理程式：負責從 applications 表中刪除一筆紀錄

session_start();
require_once 'db.php';

// 1. 安全檢查：確認已登入
if (!isset($_SESSION['account'])) {
    die("請先登入。");
}

// 2. 獲取參數：要刪除的報名紀錄 ID (不是 job_id，而是 applications.id)
$app_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$account = $_SESSION['account'];

if ($app_id > 0) {
    // 3. 執行刪除 SQL
    // 重要安全機制：必須加上 AND user_account = '$account'
    // 這是為了防止使用者透過修改網址參數 (GET) 去刪除「別人」的報名紀錄
    $sql = "DELETE FROM applications WHERE id = $app_id AND user_account = '$account'";
    
    if (mysqli_query($conn, $sql)) {
        // 檢查實際上是否有資料被刪除
        if (mysqli_affected_rows($conn) > 0) {
            // 刪除成功
            echo "<script>
                    alert('已成功取消報名。');
                    window.location.href = 'my_applications.php'; // 導回列表頁
                  </script>";
        } else {
            // SQL 執行成功但沒刪除資料 (可能是 ID 錯誤或該紀錄不屬於此人)
            echo "<script>
                    alert('找不到該筆報名資料，或您無權限取消。');
                    window.location.href = 'my_applications.php';
                  </script>";
        }
    } else {
        // SQL 語法錯誤
        echo "刪除失敗: " . mysqli_error($conn);
    }
} else {
    // ID 無效
    echo "<script>
            alert('無效的操作。');
            window.location.href = 'my_applications.php';
          </script>";
}

mysqli_close($conn);
?>