<?php
// notify.php - FINAL VERSION
// Feature: Sends internal DB notification AND real Email using PHPMailer
session_start();

// Load Composer packages
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Load .env variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

$title = "發送站內通知與信件";
include "header.php";
require_once 'db.php'; // Uses your Port 3307 connection

// --- Permission Check ---
$is_admin = false;
if (!empty($_SESSION['role']) && (strtoupper(trim($_SESSION['role'])) === 'M' || strtoupper(trim($_SESSION['role'])) === 'T')) {
    $is_admin = true;
}

if (!$is_admin) {
    die("<div class='container mt-5 alert alert-danger'>Access Denied: 只有管理員可以訪問此頁面。</div>");
}

// --- Logic ---
$events_sql = "SELECT DISTINCT j.postid, j.company, j.content 
               FROM job j 
               JOIN applications a ON j.postid = a.job_id 
               ORDER BY j.postid DESC";
$events_result = mysqli_query($conn, $events_sql);
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_job_id = intval($_POST['target_job_id']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message_body = mysqli_real_escape_string($conn, $_POST['message']);

    if ($target_job_id > 0 && !empty($subject) && !empty($message_body)) {
        
        // Find users who applied + their emails
        $recipients_sql = "SELECT a.user_account, u.email 
                           FROM applications a 
                           JOIN user u ON a.user_account = u.account 
                           WHERE a.job_id = $target_job_id";
        $recipients_result = mysqli_query($conn, $recipients_sql);
        
        $count = 0;
        $email_count = 0;
        
        // Setup PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? '';
            $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->setFrom($_ENV['SMTP_USER'], 'Camp System Admin');
            $mail->CharSet = 'UTF-8'; 
        } catch (Exception $e) {
            $msg .= "<br>Mailer Config Error: {$mail->ErrorInfo}";
        }

        while ($row = mysqli_fetch_assoc($recipients_result)) {
            $user_acc = $row['user_account'];
            $user_email = $row['email'];
            
            // A. Internal Notification
            $insert_sql = "INSERT INTO notifications (user_account, subject, message) 
                           VALUES ('$user_acc', '$subject', '$message_body')";
            mysqli_query($conn, $insert_sql);
            $count++;

            // B. Email Sending
            if (!empty($user_email) && !empty($_ENV['SMTP_USER'])) {
                try {
                    $mail->clearAddresses();
                    $mail->addAddress($user_email); 
                    $mail->Subject = $subject;
                    $mail->Body    = $message_body;
                    $mail->send();
                    $email_count++;
                } catch (Exception $e) {
                    // Continue on error
                }
            }
        }
        
        $msg = "<div class='alert alert-success'>
                    <strong>發送成功！</strong><br>
                    站內通知: $count 位<br>
                    Email 寄送: $email_count 封
                </div>";
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
<?php include "footer.php"; ?>