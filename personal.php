<?php
// personal.php
// --------------------------------------------------------
// 用途：個人資料管理頁面
// 功能包含：
// 1. 顯示個人資料
// 2. 更新姓名
// 3. 上傳/更新頭像
// 4. 修改密碼
// --------------------------------------------------------

session_start();
include "header.php";

// 1. 權限檢查：確認使用者已登入
if (empty($_SESSION['account'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// 2. 建立資料庫連線 (此處重複建立連線，實務上可改用 require 'db.php')
$servername = "localhost:3307";
$dbname = "practice";
$username = "root";

// 建立新的 mysqli 物件
$conn = new mysqli($servername, $username, "", $dbname);
// 檢查連線錯誤
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 3. 讀取目前使用者的詳細資料
$user = $_SESSION['account'];
// 使用 Prepare Statement 查詢
$stmt = $conn->prepare("SELECT * FROM user WHERE account=?");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc(); // 將資料取出至 $row

// 若找不到資料 (理論上不會發生，除非 Session 過期或帳號被刪除)
if (!$row) {
    die("找不到使用者資料");
}

$message = ""; // 用於儲存操作結果訊息

// 4. 處理 POST 表單提交 (更新資料)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 接收各欄位輸入 (使用 trim 去除前後空白)
    $name = trim($_POST['name'] ?? '');
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 預設頭像路徑維持不變 (使用 DB 中原本的值)
    $avatar_path = $row['avatar'];

    // 4-1. 處理圖片上傳
    // 檢查是否有上傳檔案且無錯誤 (UPLOAD_ERR_OK = 0)
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['avatar']['tmp_name']; // 暫存路徑
        $fileName = $_FILES['avatar']['name'];        // 原始檔名
        $fileSize = $_FILES['avatar']['size'];        // 檔案大小
        $fileType = $_FILES['avatar']['type'];        // 檔案類型

        // 解析副檔名
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // 設定允許的圖片格式
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

        // 檢查副檔名是否合法
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // 使用原始檔名 (不防止檔名衝突)
            $newFileName = $fileName;
            // 指定上傳目錄
            $uploadFileDir = 'uploads/avatars/';

            // 若目錄不存在，則自動建立 (權限 0777)
            if (!file_exists($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            // 組合完整目標路徑
            $dest_path = $uploadFileDir . $newFileName;

            // 將檔案從暫存區移動到目標資料夾
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $avatar_path = $dest_path; // 更新路徑變數準備寫入 DB
            } else {
                $message .= " 圖片上傳失敗。";
            }
        } else {
            $message .= " 不支援的圖片格式。";
        }
    }

    // 4-2. 處理密碼修改邏輯
    $update_password = false;
    // 如果有輸入新密碼，才進行密碼變更檢查
    if (!empty($new_password)) {
        // 檢查舊密碼是否正確
        if ($old_password !== $row['password']) {
            $message = "舊密碼輸入錯誤！";
        } elseif ($new_password !== $confirm_password) {
            // 檢查兩次新密碼是否一致
            $message = "新密碼輸入不一致！";
        } else {
            // 通過檢查，標記為需要更新密碼
            $update_password = true;
        }
    }

    // 4-3. 執行資料庫更新
    // 確保沒有錯誤訊息才執行
    if (empty($message) || strpos($message, '失敗') === false) {
        if ($update_password) {
            // 分支 A: 更新 姓名 + 密碼 + 頭像
            $stmt = $conn->prepare("UPDATE user SET name=?, password=?, avatar=? WHERE account=?");
            $stmt->bind_param("ssss", $name, $new_password, $avatar_path, $user);
            // 更新記憶體中的 $row，以便下方的 HTML 顯示最新資料
            $row['password'] = $new_password;
        } else {
            // 分支 B: 只更新 姓名 + 頭像 (不改密碼)
            $stmt = $conn->prepare("UPDATE user SET name=?, avatar=? WHERE account=?");
            $stmt->bind_param("sss", $name, $avatar_path, $user);
        }

        // 執行 UPDATE
        if ($stmt->execute()) {
            $message = "資料更新成功！";

            // 更新 Session 中的姓名 (因為 header 等處會用到)
            $_SESSION['name'] = $name;
            // 更新 $row 中的頭像路徑
            $row['avatar'] = $avatar_path;
        } else {
            $message = "資料更新失敗！";
        }
    }
}
?>

<div class="container my-5">
    <h2>個人資料</h2>

    <!-- 顯示訊息區塊 -->
    <?php if ($message): ?>
        <!-- 根據訊息內容判斷要顯示綠色 (Success) 或紅色 (Danger) -->
        <div class="alert <?php echo strpos($message, '成功') !== false ? 'alert-success' : 'alert-danger'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- 資料表單 (支援檔案上傳需加 enctype) -->
    <form method="post" enctype="multipart/form-data">
        <div class="row">
            <!-- 左側：頭像顯示與上傳 -->
            <div class="col-md-4 text-center mb-4">
                <?php
                // 決定顯示哪張圖片
                // 預設圖片
                $avatarPath = "uploads/avatars/default.png";
                // 若使用者有自訂頭像且檔案存在，則使用自訂頭像
                if (!empty($row['avatar']) && file_exists($row['avatar'])) {
                    $avatarPath = $row['avatar'];
                }
                ?>
                <!-- 顯示頭像圖片 (圓形剪裁) -->
                <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="User Avatar"
                    class="img-thumbnail rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">

                <!-- 檔案上傳輸入框 -->
                <div class="mb-3">
                    <label for="avatar" class="form-label">更換頭像</label>
                    <input type="file" class="form-control" id="avatar" name="avatar"
                        accept="image/png, image/jpeg, image/gif">
                </div>
            </div>

            <!-- 右側：個人資料與密碼 -->
            <div class="col-md-8">
                <!-- 帳號 (唯讀) -->
                <div class="mb-3">
                    <label for="account" class="form-label">帳號</label>
                    <input type="text" class="form-control" id="account"
                        value="<?php echo htmlspecialchars($row['account']); ?>" readonly>
                </div>

                <!-- 身分 (唯讀) -->
                <div class="mb-3">
                    <label for="role" class="form-label">身分</label>
                    <input type="text" class="form-control" id="role"
                        value="<?php echo htmlspecialchars($row['role']); ?>" readonly>
                </div>

                <!-- 姓名 (可修改) -->
                <div class="mb-3">
                    <label for="name" class="form-label">姓名</label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="<?php echo htmlspecialchars($row['name']); ?>" required>
                </div>

                <hr>
                <h2>修改密碼</h2>

                <!-- 舊密碼 -->
                <div class="mb-3">
                    <label for="old_password" class="form-label">舊密碼</label>
                    <input type="password" class="form-control" id="old_password" name="old_password"
                        autocomplete="off">
                </div>

                <!-- 新密碼 -->
                <div class="mb-3">
                    <label for="new_password" class="form-label">新密碼</label>
                    <input type="password" class="form-control" id="new_password" name="new_password"
                        autocomplete="off">
                </div>

                <!-- 確認新密碼 -->
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">確認新密碼</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                        autocomplete="off">
                </div>

                <!-- 按鈕區 -->
                <button type="submit" class="btn btn-success">更新</button>
                <a href="index.php" class="btn btn-secondary">取消</a>
            </div>
        </div>
    </form>
</div>

<?php include "footer.php"; ?>