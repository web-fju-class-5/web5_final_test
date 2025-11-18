<?php
session_start();
include "header.php";

// 確認使用者已登入
if (empty($_SESSION['account'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$servername = "localhost:3307";
$dbname = "practice";
$username = "root";
$conn = new mysqli($servername, $username, "", $dbname);
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 取得登入使用者資料
$user = $_SESSION['account'];
$stmt = $conn->prepare("SELECT * FROM user WHERE account=?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) {
    die("找不到使用者資料");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 如果有填密碼欄位
    if ($old_password === $new_password && $new_password === $confirm_password) {
        
        if ($old_password !== $row['password']) {
            $message = "舊密碼輸入錯誤！";
        } elseif ($new_password !== $confirm_password) {
            $message = "新密碼輸入不一致！";
        } else {
            
            $stmt = $conn->prepare("UPDATE user SET name=?, password=? WHERE account=?");
            $stmt->bind_param("sss", $name, $new_password, $user);
            if ($stmt->execute()) {
                $message = "資料更新成功！";
                $_SESSION['name'] = $name;
                $row['password'] = $new_password;
            } else {
                $message = "資料更新失敗！";
            }
        }
    } else {
        
        $stmt = $conn->prepare("UPDATE user SET name=? WHERE account=?");
        $stmt->bind_param("ss", $name, $user);
        if ($stmt->execute()) {
            $message = "姓名更新成功！";
            $_SESSION['name'] = $name;
        } else {
            $message = "姓名更新失敗！";
        }
    }
}
?>

<div class="container my-5">
    <h2>個人資料</h2>

    <?php if ($message): ?>
        <div class="alert <?php echo strpos($message, '成功') !== false ? 'alert-success' : 'alert-danger'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="account" class="form-label">帳號</label>
            <input type="text" class="form-control" id="account" value="<?php echo htmlspecialchars($row['account']); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">身分</label>
            <input type="text" class="form-control" id="role" value="<?php echo htmlspecialchars($row['role']); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">姓名</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
        </div>
        <hr>
        <h2>修改密碼</h2>

        <div class="mb-3">
            <label for="old_password" class="form-label">舊密碼</label>
            <input type="password" class="form-control" id="old_password" name="old_password" autocomplete="off">
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label">新密碼</label>
            <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="off">
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">確認新密碼</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="off">
        </div>
        <button type="submit" class="btn btn-success">更新</button>
        <a href="index.php" class="btn btn-secondary">取消</a>
    </form>
</div>

<?php include "footer.php"; ?>
