<?php
// apply.php - FINAL VERSION
// Feature: Registers user AND sends automatic confirmation email
session_start();
require_once 'db.php';
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Load .env variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

// 1. Check Login
if (!isset($_SESSION['account'])) {
    die("請先登入才能報名。 <a href='index.php'>回首頁</a>");
}

$postid = isset($_GET['postid']) ? intval($_GET['postid']) : 0;
$account = $_SESSION['account'];

if ($postid <= 0) {
    die("無效的活動 ID。 <a href='index.php'>回首頁</a>");
}

// 2. Check Duplicate
$check_sql = "SELECT id FROM applications WHERE job_id = $postid AND user_account = '$account'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    echo "<script>
            alert('您已經報名過這個活動囉！');
            window.location.href = 'index.php';
          </script>";
    exit;
}

// 3. Insert Application
$insert_sql = "INSERT INTO applications (job_id, user_account) VALUES ($postid, '$account')";

if (mysqli_query($conn, $insert_sql)) {
    
    // --- START EMAIL LOGIC ---
    $info_sql = "SELECT u.email, u.name, j.company, j.content, j.pdate 
                 FROM user u, job j 
                 WHERE u.account = '$account' AND j.postid = $postid";
    $info_result = mysqli_query($conn, $info_sql);
    $info = mysqli_fetch_assoc($info_result);

    if ($info && !empty($info['email'])) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? '';
            $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($_ENV['SMTP_USER'], 'Camp System');
            $mail->addAddress($info['email'], $info['name']);

            $mail->isHTML(true);
            $mail->Subject = '【報名成功】' . $info['company'] . ' - ' . $info['content'];
            $mail->Body    = "
                <h3>Hi, {$info['name']}</h3>
                <p>恭喜您！您已成功報名以下活動：</p>
                <ul>
                    <li><strong>活動：</strong> {$info['company']}</li>
                    <li><strong>內容：</strong> {$info['content']}</li>
                    <li><strong>日期：</strong> {$info['pdate']}</li>
                </ul>
                <p>請準時出席！</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            // Log error but allow success
        }
    }
    // --- END EMAIL LOGIC ---

    header("Location: apply_success.php?postid=$postid");
    exit;

} else {
    echo "報名失敗: " . mysqli_error($conn);
    echo "<br><a href='index.php'>回首頁</a>";
}
mysqli_close($conn);
?>