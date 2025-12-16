<?php
// notify.php
// --------------------------------------------------------
// 用途：管理員發送通知頁面
// 功能：
// 1. 驗證權限 (限制僅管理員或老師可存取)
// 2. 列出所有活動供選擇 (下拉選單)
// 3. 發送通知給所選活動的所有報名者 (整批寫入 notifications 表)
// --------------------------------------------------------

session_start();
$title = "發送站內通知";
include "header.php";
require_once 'db.php';

// --- 1. 權限驗證 (最重要) ---
// 判斷 role 是否為 'M' (Manager) 或 'T' (Teacher)
$is_admin = false;
if (!empty($_SESSION['role']) && (strtoupper(trim($_SESSION['role'])) === 'M' || strtoupper(trim($_SESSION['role'])) === 'T')) {
    $is_admin = true;
}

// 若非管理員，顯示拒絕存取畫面並中止程式
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
    include "footer.php";
    exit; // 務必 exit，防止後續程式碼被執行
}

// --- 以下為管理員可見內容 ---

// 2. 獲取活動列表 (用於準備下拉選單的選項)
// 邏輯: 從 job 表取出活動，並 JOIN applications 表，只選出「有人報名」的活動 DISTINCT 去重
// 這樣如果活動沒人報名，就不會出現在清單中 (因為發了也沒人收)
$events_sql = "SELECT DISTINCT j.postid, j.company, j.content 
               FROM job j 
               JOIN applications a ON j.postid = a.job_id 
               ORDER BY j.postid DESC";
$events_result = mysqli_query($conn, $events_sql);

$msg = ""; // 用於儲存操作後的提示訊息

// 3. 處理表單提交 (當按下「確認發送」)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 接收 POST 資料
    $target_job_id = intval($_POST['target_job_id']); // 目標活動 ID
    // 使用 real_escape_string 處理輸入文字，防止 SQL Injection
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message_body = mysqli_real_escape_string($conn, $_POST['message']);

    // 檢查必填欄位是否都有值
    if ($target_job_id > 0 && !empty($subject) && !empty($message_body)) {

        // 步驟 A: 查詢該活動的所有報名者帳號
        $recipients_sql = "SELECT user_account FROM applications WHERE job_id = $target_job_id";
        $recipients_result = mysqli_query($conn, $recipients_sql);

        $count = 0; // 用來計算成功發送幾筆

        // 步驟 B: 跑迴圈，逐一插入通知紀錄到 notifications 表
        while ($row = mysqli_fetch_assoc($recipients_result)) {
            $user_acc = $row['user_account'];

            // 寫入 SQL：包含 接收者帳號、標題、內容
            $insert_sql = "INSERT INTO notifications (user_account, subject, message) 
                           VALUES ('$user_acc', '$subject', '$message_body')";

            // 執行插入
            if (mysqli_query($conn, $insert_sql)) {
                $count++;
            }
        }

        // 檢查發送結果
        if ($count > 0) {
            // 成功訊息 (Bootstrap Alert)
            $msg = "<div class='alert alert-success alert-dismissible fade show'>
                        <strong>發送成功！</strong> 已通知 $count 位使用者。
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
        } else {
            // 可能是該活動剛好沒人報名 (理論上前面 SQL 篩選過，但雙重保險)
            $msg = "<div class='alert alert-warning'>發送失敗，或該活動無人報名。</div>";
        }

    } else {
        // 欄位缺漏
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

    <!-- 顯示操作訊息 -->
    <?= $msg ?>

    <!-- 發送表單卡片 -->
    <div class="card shadow-sm border-info">
        <div class="card-header bg-info text-white fw-bold">
            撰寫新通知
        </div>
        <div class="card-body">
            <form method="POST" action="notify.php">

                <!-- 下拉選單：選擇接收群組 -->
                <div class="mb-3">
                    <label for="target_job_id" class="form-label fw-bold">接收對象 (活動群組)</label>
                    <select name="target_job_id" id="target_job_id" class="form-select" required>
                        <option value="">-- 請選擇活動 --</option>
                        <?php
                        // 動態生成選項
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

                <!-- 標題輸入框 -->
                <div class="mb-3">
                    <label for="subject" class="form-label fw-bold">通知標題</label>
                    <input type="text" class="form-control" id="subject" name="subject" placeholder="例如：活動地點異動"
                        required>
                </div>

                <!-- 內容輸入框 -->
                <div class="mb-3">
                    <label for="message" class="form-label fw-bold">通知內容</label>
                    <textarea class="form-control" id="message" name="message" rows="6" placeholder="請輸入詳細內容..."
                        required></textarea>
                </div>

                <!-- 送出按鈕 -->
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