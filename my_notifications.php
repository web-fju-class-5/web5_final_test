<?php
// my_notifications.php
// --------------------------------------------------------
// 用途：顯示當前使用者的通知中心
// 功能：
// 1. 列出 notifications 表中發送給當前 user_account 的訊息
// 2. 顯示刪除按鈕
// 3. 提供「發送新通知」按鈕 (管理員用)
// --------------------------------------------------------

// 1. 啟動 Session
session_start();

// 2. 模擬登入 (開發測試用，尚未登入時自動給予身分)
if (!isset($_SESSION['account'])) {
    $_SESSION['account'] = 'user1';
    $_SESSION['role'] = 'S';
    $_SESSION['name'] = '小明';
}

$title = "我的訊息";
include "header.php";
require_once 'db.php';

$account = $_SESSION['account'];

// --- 3. 處理刪除訊息邏輯 (收到 GET delete_id) ---
if (isset($_GET['delete_id'])) {
    // 轉整數防止 SQL Injection
    $del_id = intval($_GET['delete_id']);

    // 準備刪除 SQL
    // 必須加上 user_account 條件，確保只能刪除自己的訊息
    $del_sql = "DELETE FROM notifications WHERE id = $del_id AND user_account = '$account'";

    if (mysqli_query($conn, $del_sql)) {
        // 刪除成功，重新導向回本頁 (清除 URL 參數，避免重整時重複執行)
        header("Location: my_notifications.php");
        exit;
    } else {
        echo "<script>alert('刪除失敗');</script>";
    }
}

// --- 4. 查詢通知列表 ---
// 根據帳號查詢，並依照建立時間倒序排列 (最新的在最上面)
$sql = "SELECT * FROM notifications WHERE user_account = '$account' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">

    <!-- 標題區塊 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>我的訊息中心</h2>

        <!-- 
          發送新通知按鈕
          連結到 notify.php (後端發送頁面)
          雖然按鈕人人可見，但 notify.php 內部有做管理員權限檢查
        -->
        <a href="notify.php" class="btn btn-primary">
            <i class="bi bi-envelope-plus"></i> 發送新通知 (管理員)
        </a>
    </div>

    <!-- 顯示當前使用者身分 -->
    <div class="alert alert-light border">
        使用者：<strong><?= htmlspecialchars($_SESSION['name']) ?></strong> (<?= htmlspecialchars($_SESSION['role']) ?>)
    </div>

    <!-- 訊息列表區 -->
    <div class="row">
        <div class="col-md-12">
            <?php
            // 檢查是否有查詢結果
            if ($result && mysqli_num_rows($result) > 0) {
                // 迴圈輸出每一則訊息
                while ($row = mysqli_fetch_assoc($result)) {
                    // 格式化時間：年-月-日 時:分
                    $time = date('Y-m-d H:i', strtotime($row['created_at']));
                    ?>
                    <!-- 訊息卡片 -->
                    <div class="card mb-3 shadow-sm border-start border-4 border-info">
                        <!-- 卡片標頭：顯示標題與時間 -->
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <strong class="fs-5 text-dark"><?= htmlspecialchars($row['subject']) ?></strong>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> <?= $time ?>
                            </small>
                        </div>
                        <!-- 卡片內容：顯示訊息本文 -->
                        <div class="card-body">
                            <!-- white-space: pre-wrap 保留換行符號格式 -->
                            <p class="card-text" style="white-space: pre-wrap;"><?= htmlspecialchars($row['message']) ?></p>

                            <!-- 刪除按鈕 -->
                            <div class="text-end">
                                <a href="my_notifications.php?delete_id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('確定要刪除這則訊息嗎？此動作無法復原。');">
                                    刪除訊息
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                // 若無訊息，顯示空狀態提示
                echo "<div class='alert alert-secondary text-center py-5'>
                        <h4>目前沒有任何新訊息</h4>
                        <p>當有活動通知時，訊息會顯示在這裡。</p>
                      </div>";
            }
            ?>
        </div>
    </div>

    <!-- 返回首頁 -->
    <div class="mt-3">
        <a href="index.php" class="btn btn-secondary">回首頁</a>
    </div>
</div>

<?php
// 關閉資料庫連線
mysqli_close($conn);
?>
<?php
// 引入頁尾
include "footer.php";
?>