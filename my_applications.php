<?php
// my_applications.php
// --------------------------------------------------------
// 使用者功能：
// 顯示當前登入使用者所有報名過的活動列表 (查詢 applications 表)。
// 提供「取消報名」按鈕。
// --------------------------------------------------------

session_start();

// 模擬登入 (測試用)
if (!isset($_SESSION['account'])) {
    $_SESSION['account'] = 'user1';
    $_SESSION['name'] = '小明';
    $_SESSION['role'] = 'S';
}

$title = "我的報名紀錄";
include "header.php";
require_once 'db.php';

$account = $_SESSION['account'];

// 建構 SQL 查詢
// 使用 JOIN 將 applications (報名表) 與 job (活動表) 連接
// 目標：取得使用者報名了哪些活動的詳細資料
$sql = "SELECT a.id AS app_id, a.applied_at, j.postid, j.company, j.content, j.pdate 
        FROM applications a 
        JOIN job j ON a.job_id = j.postid 
        WHERE a.user_account = '$account' 
        ORDER BY a.applied_at DESC";

$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h2 class="mb-4">我的報名紀錄</h2>
    
    <div class="alert alert-light border">
        使用者：<strong><?= htmlspecialchars($_SESSION['name']) ?></strong> (<?= htmlspecialchars($account) ?>)
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            已報名活動列表
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">報名時間</th>
                        <th scope="col">主辦單位</th>
                        <th scope="col">內容</th>
                        <th scope="col">活動日期</th>
                        <th scope="col" class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // 格式化時間
                            $apply_time = date('Y-m-d H:i', strtotime($row['applied_at']));
                    ?>
                        <tr>
                            <td class="text-muted small"><?= $apply_time ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['company']) ?></td>
                            <td><?= htmlspecialchars($row['content']) ?></td>
                            <td><?= htmlspecialchars($row['pdate']) ?></td>
                            <td class="text-center">
                                <!-- 取消報名按鈕 -->
                                <!-- 傳遞的是報名紀錄 ID (app_id)，而非活動 ID -->
                                <a href="cancel_application.php?id=<?= $row['app_id'] ?>" 
                                   class="btn btn-outline-danger btn-sm"
                                   onclick="return confirm('確定要取消「<?= htmlspecialchars($row['company']) ?>」的報名嗎？');">
                                   取消報名
                                </a>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="5" class="text-center py-4 text-muted">目前還沒有報名任何活動喔！ <a href="index.php">去逛逛</a></td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="index.php" class="btn btn-secondary">回首頁</a>
    </div>
</div>

<?php mysqli_close($conn); ?>
<?php 
include "footer.php"; 
?>