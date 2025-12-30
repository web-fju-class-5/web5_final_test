<?php
// apply.php - BULLETPROOF VERSION
ob_start(); // 1. Start buffering to prevent "Headers Sent" errors
session_start();
require_once 'db.php';

// 2. Load Libraries (Safely)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    // If library is missing, just redirect and skip email
    header("Location: apply_success.php?postid=" . ($_GET['postid'] ?? 0));
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// 3. Load .env (Safely)
// We wrap this in Try/Catch so a bad .env file won't crash the site
if (file_exists(__DIR__ . '/.env')) {
    try {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    } catch (\Throwable $e) {
        // Ignore .env errors silently
    }
}

// 4. Check Login
if (!isset($_SESSION['account'])) {
    die("請先登入才能報名。 <a href='index.php'>回首頁</a>");
}

$postid = isset($_GET['postid']) ? intval($_GET['postid']) : 0;
$account = $_SESSION['account'];

if ($postid <= 0) {
    die("無效的活動 ID。 <a href='index.php'>回首頁</a>");
}

// 5. Check Duplicate
$check_sql = "SELECT id FROM applications WHERE job_id = $postid AND user_account = '$account'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    echo "<script>
            alert('您已經報名過這個活動囉！');
            window.location.href = 'index.php';
          </script>";
    exit;
}

// 6. Insert Application
$insert_sql = "INSERT INTO applications (job_id, user_account) VALUES ($postid, '$account')";

if (mysqli_query($conn, $insert_sql)) {
    
    // --- START EMAIL LOGIC ---
    // We use try/catch broadly here so NOTHING stops the redirect
    try {
        $info_sql = "SELECT u.email, u.name, j.company, j.content, j.pdate 
                     FROM user u, job j 
                     WHERE u.account = '$account' AND j.postid = $postid";
        $info_result = mysqli_query($conn, $info_sql);
        $info = mysqli_fetch_assoc($info_result);

        if ($info && !empty($info['email'])) {
            $mail = new PHPMailer(true);
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
            $mail->Subject = '【報名成功】' . $info['company'];
            $mail->Body    = "Hi {$info['name']}, 報名成功！";

            $mail->send();
        }
    } catch (\Throwable $e) {
        // If Email fails, DO NOTHING. Just continue to redirect.
    }
    // --- END EMAIL LOGIC ---

    // 7. Force Redirect
    ob_clean(); // Clear any error text
    header("Location: apply_success.php?postid=$postid");
    exit;

} else {
    echo "報名失敗: " . mysqli_error($conn);
}
mysqli_close($conn);
ob_end_flush();
?>