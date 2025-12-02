<?php
// personal.php - FINAL VERSION
// Feature: Avatar Upload & Profile Update
session_start();
include "header.php";
require_once "db.php"; // IMPORTANT: Uses shared db.php (Port 3307)

// Check login
if (empty($_SESSION['account'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user = $_SESSION['account'];
// Fetch user data
$stmt = $conn->prepare("SELECT * FROM user WHERE account=?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) die("找不到使用者資料");

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? ''); 
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- 1. HANDLE AVATAR UPLOAD ---
    if (isset($_FILES['avatar_upload']) && $_FILES['avatar_upload']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['avatar_upload']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && getimagesize($_FILES['avatar_upload']['tmp_name'])) {
            if (!is_dir('uploads/avatars')) {
                mkdir('uploads/avatars', 0755, true);
            }
            $new_filename = $user . '_' . time() . '.' . $ext;
            $dest = 'uploads/avatars/' . $new_filename;
            
            if (move_uploaded_file($_FILES['avatar_upload']['tmp_name'], $dest)) {
                $stmt_img = $conn->prepare("UPDATE user SET avatar=? WHERE account=?");
                $stmt_img->bind_param("ss", $dest, $user);
                $stmt_img->execute();
                $row['avatar'] = $dest; 
                $message .= " 頭像已更新！";
            }
        } else {
            $message .= " <span class='text-danger'>錯誤：格式不符或非圖片檔。</span>";
        }
    }

    // --- 2. UPDATE INFO & PASSWORD ---
    if (!empty($new_password)) {
        if ($old_password !== $row['password']) {
            $message .= " <span class='text-danger'>舊密碼錯誤。</span>";
        } elseif ($new_password !== $confirm_password) {
            $message .= " <span class='text-danger'>新密碼不一致。</span>";
        } else {
            $update_sql = "UPDATE user SET name=?, email=?, password=? WHERE account=?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssss", $name, $email, $new_password, $user);
            if ($stmt->execute()) {
                $message .= " 資料與密碼更新成功！";
                $_SESSION['name'] = $name;
                $row['password'] = $new_password;
                $row['email'] = $email;
            }
        }
    } else {
        $update_sql = "UPDATE user SET name=?, email=? WHERE account=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sss", $name, $email, $user);
        if ($stmt->execute()) {
            $message .= " 資料已儲存。";
            $_SESSION['name'] = $name;
            $row['email'] = $email;
        }
    }
}
?>

<div class="container my-5">
    <h2>個人資料設定</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
        <div class="row mb-4 align-items-center">
            <div class="col-md-3 text-center">
                <?php $avatar_path = !empty($row['avatar']) ? $row['avatar'] : 'https://via.placeholder.com/150'; ?>
                <img src="<?= htmlspecialchars($avatar_path) ?>" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
            </div>
            <div class="col-md-9">
                <label for="avatar_upload" class="form-label fw-bold">更換頭像</label>
                <input type="file" class="form-control" name="avatar_upload" accept="image/*">
                <div class="form-text">支援 JPG, PNG, GIF。</div>
            </div>
        </div>
        <hr>
        <div class="mb-3">
            <label class="form-label">帳號</label>
            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($row['account']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">姓名</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email (用於接收通知)</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($row['email'] ?? '') ?>" placeholder="請輸入 Email">
        </div>
        <h5 class="mt-4 text-muted">修改密碼 (選填)</h5>
        <div class="row g-2">
            <div class="col-md-4"><input type="password" class="form-control" name="old_password" placeholder="舊密碼"></div>
            <div class="col-md-4"><input type="password" class="form-control" name="new_password" placeholder="新密碼"></div>
            <div class="col-md-4"><input type="password" class="form-control" name="confirm_password" placeholder="確認新密碼"></div>
        </div>
        <div class="mt-4 text-end"><button type="submit" class="btn btn-primary px-4">儲存變更</button></div>
    </form>
</div>
<?php include "footer.php"; ?>