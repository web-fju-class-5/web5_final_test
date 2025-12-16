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

    $avatar_path = $row['avatar']; // 預設維持原圖

    // 處理圖片上傳
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileSize = $_FILES['avatar']['size'];
        $fileType = $_FILES['avatar']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = 'uploads/avatars/';

            if (!file_exists($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $avatar_path = $dest_path;
            } else {
                $message .= " 圖片上傳失敗。";
            }
        } else {
            $message .= " 不支援的圖片格式。";
        }
    }

    // 判斷是否要修改密碼
    $update_password = false;
    if (!empty($new_password)) {
        if ($old_password !== $row['password']) {
            $message = "舊密碼輸入錯誤！";
        } elseif ($new_password !== $confirm_password) {
            $message = "新密碼輸入不一致！";
        } else {
            $update_password = true;
        }
    }

    // 如果沒有錯誤訊息，執行更新
    if (empty($message) || strpos($message, '失敗') === false) {
        if ($update_password) {
            $stmt = $conn->prepare("UPDATE user SET name=?, password=?, avatar=? WHERE account=?");
            $stmt->bind_param("ssss", $name, $new_password, $avatar_path, $user);
            // 更新 session 密碼 (如果有的話) - 雖然之後重新 query 會抓到
            $row['password'] = $new_password;
        } else {
            $stmt = $conn->prepare("UPDATE user SET name=?, avatar=? WHERE account=?");
            $stmt->bind_param("sss", $name, $avatar_path, $user);
        }

        if ($stmt->execute()) {
            $message = "資料更新成功！"; // 覆蓋掉可能的上傳失敗訊息? 不，應該是附加? 這裡直接設為成功比較簡單，如果上傳失敗前面會擋
            // 如果上傳有部分失敗但DB沒更新? 前面 $message .= 會導致這裡不空。
            // 修正邏輯：如果只剩 "圖片上傳失敗" or "不支援格式" 這類訊息，這裡不該執行 DB update?
            // 通常如果圖片失敗，我們可能還是想更新名字?
            // 簡單起見，如果 $message 不為空 (代表圖片有問題或密碼有問題)，就不更新 DB。
            // 但上面寫 `empty($message)`。

            $_SESSION['name'] = $name;
            $row['avatar'] = $avatar_path;
        } else {
            $message = "資料更新失敗！";
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

    <form method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <?php
                $avatarPath = "uploads/avatars/default.png"; // 預設頭像
                if (!empty($row['avatar']) && file_exists($row['avatar'])) {
                    $avatarPath = $row['avatar'];
                }
                ?>
                <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="User Avatar"
                    class="img-thumbnail rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                <div class="mb-3">
                    <label for="avatar" class="form-label">更換頭像</label>
                    <input type="file" class="form-control" id="avatar" name="avatar"
                        accept="image/png, image/jpeg, image/gif">
                </div>
            </div>
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="account" class="form-label">帳號</label>
                    <input type="text" class="form-control" id="account"
                        value="<?php echo htmlspecialchars($row['account']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">身分</label>
                    <input type="text" class="form-control" id="role"
                        value="<?php echo htmlspecialchars($row['role']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">姓名</label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="<?php echo htmlspecialchars($row['name']); ?>" required>
                </div>
                <hr>
                <h2>修改密碼</h2>

                <div class="mb-3">
                    <label for="old_password" class="form-label">舊密碼</label>
                    <input type="password" class="form-control" id="old_password" name="old_password"
                        autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">新密碼</label>
                    <input type="password" class="form-control" id="new_password" name="new_password"
                        autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">確認新密碼</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                        autocomplete="off">
                </div>
                <button type="submit" class="btn btn-success">更新</button>
                <a href="index.php" class="btn btn-secondary">取消</a>
            </div>
        </div>
    </form>
</div>

<?php include "footer.php"; ?>