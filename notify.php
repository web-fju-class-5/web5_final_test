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
    <h2>發送活動通知 (Email + 站內信)</h2>
    <?= $msg ?>
    <form method="POST" action="notify.php" class="card p-4 shadow-sm border-info">
        <div class="mb-3">
            <label class="form-label fw-bold">選擇活動群組</label>
            <select name="target_job_id" class="form-select" required>
                <option value="">-- 請選擇 --</option>
                <?php while ($row = mysqli_fetch_assoc($events_result)): ?>
                    <option value="<?= $row['postid'] ?>">
                        <?= htmlspecialchars($row['company'] . " - " . $row['content']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">標題</label>
            <input type="text" name="subject" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">內容</label>
            <textarea name="message" class="form-control" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-info text-white">發送通知</button>
    </form>
</div>
<?php include "footer.php"; ?>