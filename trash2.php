<?php
// 引入共用 head 和導覽列
include "header.php";

// 連接資料庫
$servername = "localhost:3307";
$dbname = "practice";
$username = "root";
$conn = new mysqli($servername, $username, "", $dbname);
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 取得 postid
$postid = $_GET['postid'] ?? null;
if (!$postid) {
    die("沒有指定活動 ID");
}

// 表單送出處理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    $stmt = $conn->prepare("UPDATE event SET name=?, description=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $description, $postid);
    $stmt->execute();

    header("Location: index.php"); // 更新完成後導回首頁
    exit;
}

// 如果不是送出，讀取活動資料
$stmt = $conn->prepare("SELECT * FROM event WHERE id=?");
$stmt->bind_param("i", $postid);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) {
    die("找不到該活動資料");
}
?>

<div class="container my-5">
    <h2>編輯活動</h2>
    <form method="post">
        <div class="mb-3">
            <label for="name" class="form-label">活動名稱</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">活動描述</label>
            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($row['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">更新</button>
        <a href="index.php" class="btn btn-secondary">取消</a>
    </form>
</div>

<?php
include "footer.php";
?>
