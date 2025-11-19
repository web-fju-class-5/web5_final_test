<?php
// notify.php
// --------------------------------------------------------
// 管理員專屬頁面：
// 功能：選擇一個活動，向所有報名該活動的使用者發送站內通知。
// 邏輯：
// 1. 檢查權限 (非管理員禁止訪問)
// 2. 列出有報名紀錄的活動
// 3. 將通知訊息寫入 notifications 表
// --------------------------------------------------------

session_start();
$title = "發送站內通知";
include "header.php";
require_once 'db.php';

// --- 1. 嚴格的權限檢查 ---
// 只有角色為 'M' (Manager) 或 'T' (Teacher) 才能執行
$is_admin = false;
if (!empty($_SESSION['role']) && (strtoupper(trim($_SESSION['role'])) === 'M' || strtoupper(trim($_SESSION['role'])) === 'T')) {
    $is_admin = true;
}

// 如果權限不足，顯示錯誤畫面並停止程式
if (!$is_admin) {
    ?>
    <div class="container mt-5">
        <div class="alert alert-danger shadow text-center p-5">
            <h2 class="display-1"><i class="bi bi-lock-fill"></i></h2>
            <h3 class="mt-3">權限不足 (Access Denied)</h3>
            <p class="lead">抱歉，只有<strong>管理員</strong>可以訪問此頁面並發送通知。</p>
            <hr>
            <a href="my_notifications.php" class="btn btn-primary">回到我的訊息</a>
            <a href="index.php" class="btn btn-outline-secondary">回到首頁</a>
        </div>
    </div>
    <?php
    include "footer.php"; // 確保頁尾正常顯示 (如果有)
    exit; // 重要：務必終止程式，防止表單被顯示
}

// --- 以下為管理員可見內容 ---

// 2. 獲取活動列表 (用於表單下拉選單)
// SQL: 找出「有人報名」的活動 (使用 DISTINCT 去重)
$events_sql = "SELECT DISTINCT j.postid, j.company, j.content 
               FROM job j 
               JOIN applications a ON j.postid = a.job_id 
               ORDER BY j.postid DESC";
$events_result = mysqli_query($conn, $events_sql);

$msg = ""; // 儲存結果訊息

// 3. 處理表單提交 (發送通知)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_job_id = intval($_POST['target_job_id']); // 目標活動 ID
    $subject = mysqli_real_escape_string($conn, $_POST['subject']); // 標題
    $message_body = mysqli_real_escape_string($conn, $_POST['message']); // 內容

    // 檢查必填欄位
    if ($target_job_id > 0 && !empty($subject) && !empty($message_body)) {
        
        // 步驟 A: 找出該活動的所有報名者帳號
        $recipients_sql = "SELECT user_account FROM applications WHERE job_id = $target_job_id";
        $recipients_result = mysqli_query($conn, $recipients_sql);
        
        $count = 0; // 成功發送計數器
        
        // 步驟 B: 遍歷每一位報名者，寫入通知
        while ($row = mysqli_fetch_assoc($recipients_result)) {
            $user_acc = $row['user_account'];
            
            // 插入 notifications 資料表
            $insert_sql = "INSERT INTO notifications (user_account, subject, message) 
                           VALUES ('$user_acc', '$subject', '$message_body')";
            
            if (mysqli_query($conn, $insert_sql)) {
                $count++;
            }
        }
        
        if ($count > 0) {
            $msg = "<div class='alert alert-success alert-dismissible fade show'>
                        <strong>發送成功！</strong> 已通知 $count 位使用者。
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
        } else {
            $msg = "<div class='alert alert-warning'>發送失敗，或該活動無人報名。</div>";
        }

    } else {
        $msg = "<div class='alert alert-danger'>請填寫完整資訊。</div>";
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><span class="badge bg-secondary">管理後台</span> 發送站內通知</h2>
        <a href="my_notifications.php" class="btn btn-outline-secondary">回訊息列表</a>
    </div>

    <p class="text-muted">
        選擇特定活動，系統將自動發送訊息給所有該活動的報名者。
    </p>
    
    <?= $msg ?>

    <div class="card shadow-sm border-info">
        <div class="card-header bg-info text-white fw-bold">
            撰寫新通知
        </div>
        <div class="card-body">
            <!-- 發送表單 -->
            <form method="POST" action="notify.php">
                
                <div class="mb-3">
                    <label for="target_job_id" class="form-label fw-bold">接收對象 (活動群組)</label>
                    <select name="target_job_id" id="target_job_id" class="form-select" required>
                        <option value="">-- 請選擇活動 --</option>
                        <?php 
                        if ($events_result && mysqli_num_rows($events_result) > 0) {
                            while ($row = mysqli_fetch_assoc($events_result)) {
                                echo "<option value='" . $row['postid'] . "'>";
                                echo "報名【" . htmlspecialchars($row['company']) . " - " . htmlspecialchars($row['content']) . "】的成員";
                                echo "</option>";
                            }
                        } else {
                            echo "<option value='' disabled>無活動可選</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="subject" class="form-label fw-bold">通知標題</label>
                    <input type="text" class="form-control" id="subject" name="subject" placeholder="例如：活動地點異動" required>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label fw-bold">通知內容</label>
                    <textarea class="form-control" id="message" name="message" rows="6" placeholder="請輸入詳細內容..." required></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-info text-white btn-lg">確認發送通知</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php mysqli_close($conn); ?>
<?php 
include "footer.php"; 
?>