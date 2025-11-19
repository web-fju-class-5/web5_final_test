<?php
// my_notifications.php
// --------------------------------------------------------
// 使用者功能：
// 1. 顯示發送給當前使用者的所有通知列表
// 2. 提供刪除通知的功能
// 3. 提供一個「發送新通知」的按鈕 (所有人都看得到，但只有管理員能用)
// --------------------------------------------------------

session_start();

// 模擬登入 (測試用)
if (!isset($_SESSION['account'])) {
    $_SESSION['account'] = 'user1';
    $_SESSION['role'] = 'S';
    $_SESSION['name'] = '小明';
}

$title = "我的訊息";
include "header.php";
require_once 'db.php';

$account = $_SESSION['account'];

// --- 處理刪除訊息 (GET請求) ---
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    // 刪除 SQL：限制只能刪除自己的訊息 (AND user_account = '$account')
    $del_sql = "DELETE FROM notifications WHERE id = $del_id AND user_account = '$account'";
    
    if(mysqli_query($conn, $del_sql)) {
        // 刪除成功後重新導向，清除網址參數
        header("Location: my_notifications.php");
        exit;
    } else {
        echo "<script>alert('刪除失敗');</script>";
    }
}

// --- 查詢訊息列表 ---
// 依照時間倒序排列 (DESC)
$sql = "SELECT * FROM notifications WHERE user_account = '$account' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    
    <!-- 標題區塊 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>我的訊息中心</h2>
        
        <!-- 
          [發送按鈕] 
          1. 此按鈕對「所有人」顯示。
          2. 點擊後導向 notify.php。
          3. 實際權限驗證交由 notify.php 內部的 PHP 邏輯處理。
        -->
        <a href="notify.php" class="btn btn-primary">
            <i class="bi bi-envelope-plus"></i> 發送新通知 (管理員)
        </a>
    </div>
    
    <div class="alert alert-light border">
        使用者：<strong><?= htmlspecialchars($_SESSION['name']) ?></strong> (<?= htmlspecialchars($_SESSION['role']) ?>)
    </div>

    <div class="row">
        <div class="col-md-12">
            <?php
            // 檢查是否有訊息
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // 格式化時間
                    $time = date('Y-m-d H:i', strtotime($row['created_at']));
            ?>
                <!-- 訊息卡片 -->
                <div class="card mb-3 shadow-sm border-start border-4 border-info">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                        <strong class="fs-5 text-dark"><?= htmlspecialchars($row['subject']) ?></strong>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> <?= $time ?>
                        </small>
                    </div>
                    <div class="card-body">
                        <!-- 使用 nl2br 或 CSS white-space 處理換行 -->
                        <p class="card-text" style="white-space: pre-wrap;"><?= htmlspecialchars($row['message']) ?></p>
                        
                        <div class="text-end">
                            <!-- 刪除按鈕 -->
                            <a href="my_notifications.php?delete_id=<?= $row['id'] ?>" 
                               class="btn btn-outline-danger btn-sm"
                               onclick="return confirm('確定要刪除這則訊息嗎？此動作無法復原。');">
                                刪除訊息
                            </a>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                // 無訊息時顯示
                echo "<div class='alert alert-secondary text-center py-5'>
                        <h4>目前沒有任何新訊息</h4>
                        <p>當有活動通知時，訊息會顯示在這裡。</p>
                      </div>";
            }
            ?>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="index.php" class="btn btn-secondary">回首頁</a>
    </div>
</div>

<?php mysqli_close($conn); ?>
<?php 
include "footer.php"; 
?>