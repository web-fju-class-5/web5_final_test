<?php
//首頁
include "header.php";

// 1️⃣ 連接資料庫
$servername = "localhost";   
$dbname = "practice";  
$username = "root";         
// $password = "41075925";              
      

$conn = new mysqli($servername, $username, "", $dbname);

// 檢查連線
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 2️⃣ 從資料庫取得活動資料
$postid = $_GET['postid'] ?? null;

if ($postid) {
    // 若有傳 postid，就用預備語句查單筆
    $stmt = $conn->prepare("SELECT * FROM event WHERE postid = ?");
    $stmt->bind_param("i", $postid);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // 若沒傳 postid，查全部
    $result = $conn->query("SELECT * FROM event");
}

?>

<div class="container my-5">
<?php if(!empty($_SESSION['role']) && strtoupper(trim($_SESSION['role'])) === 'M'): ?>
    <a href="activity_insert.php" class="btn btn-primary">+新增活動</a>
<?php endif; ?>

<div class="row mb-4">
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100 bg-white text-dark">
                    <div class="card-body d-flex flex-column">
                        <h3 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                        <div class="mt-auto text-end">
                            <?php if(!empty($_SESSION['role']) && strtoupper(trim($_SESSION['role'])) === 'M'): ?>
                                <a href="activity_update.php?postid=<?php echo $row['id']; ?>" class="btn btn-danger">修改</a>
                                <a href="activity_delete.php?postid=<?php echo $row['id']; ?>" class="btn btn-secondary">刪除</a>
                            <?php else: ?>
                                <a href="about.php" class="btn btn-primary">報名去</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo "<p>目前沒有活動資料。</p>";
    }
    ?>
</div>
</div>

<?php
// 引入共用模板 footer.php
include "footer.php";
?>
