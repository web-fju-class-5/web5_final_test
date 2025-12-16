<?php
// cancel_application.php
// --------------------------------------------------------
// 用途：處理取消報名 (刪除報名紀錄) 的後端程式
// --------------------------------------------------------

// 1. 啟動 Session
session_start();

// 引入資料庫元件
require_once 'db.php';

// 2. 權限檢查：確認使用者已登入
// 若未登入直接中止程式
if (!isset($_SESSION['account'])) {
    die("請先登入。");
}

// 3. 獲取參數
// 從 GET 取得要刪除的 applications 表中的 id (流水號)
$app_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// 取得當前操作者帳號
$account = $_SESSION['account'];

// 檢查 ID 是否有效
if ($app_id > 0) {
    // 4. 準備刪除 SQL
    // ⚠️ 安全關鍵：必須加上 AND user_account = '$account'
    // 這是為了防止惡意使用者直接修改網址上的 ID 參數來刪除「別人」的報名紀錄
    // 確保只能刪除「屬於自己」的該筆申請
    $sql = "DELETE FROM applications WHERE id = $app_id AND user_account = '$account'";

    // 執行 SQL
    if (mysqli_query($conn, $sql)) {
        // 5. 判斷是否真的有刪除到資料 (檢查影響行數)
        // 如果影響行數 > 0，代表刪除成功
        if (mysqli_affected_rows($conn) > 0) {
            echo "<script>
                    alert('已成功取消報名。');
                    window.location.href = 'my_applications.php';
                  </script>";
        } else {
            // 如果影響行數為 0，可能原因：
            // (1) 該 ID 不存在
            // (2) 該 ID 存在但 user_account 不符 (試圖刪除別人的)
            echo "<script>
                    alert('刪除失敗：找不到資料或無權限。');
                    window.location.href = 'my_applications.php';
                  </script>";
        }
    } else {
        // SQL 執行錯誤 (語法錯誤或 DB 連線斷掉等)
        echo "SQL 錯誤: " . mysqli_error($conn);
    }
} else {
    // ID 無效
    echo "<script>
            alert('無效的操作。');
            window.location.href = 'my_applications.php';
          </script>";
}

// 關閉資料庫連線
mysqli_close($conn);
?>