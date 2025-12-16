<?php
// my_applications.php
// --------------------------------------------------------
// 用途：顯示當前登入使用者的報名紀錄
// 功能：
// 1. 列出所有報名過的活動 (PostgreSQL JOIN 查詢)
// 2. 提供取消報名的功能
// --------------------------------------------------------

// 1. 啟動 Session
session_start();

// 2. 模擬登入 (開發測試用) - 若無 Session 則自動登入 user1
if (!isset($_SESSION['account'])) {
    $_SESSION['account'] = 'user1';
    $_SESSION['name'] = '小明';
    $_SESSION['role'] = 'S';
}

$title = "我的報名紀錄";

// 引入頁首與資料庫連線
include "header.php";
require_once 'db.php';

// 取得當前使用者帳號
$account = $_SESSION['account'];

// 3. 建立 SQL 查詢
// 使用 JOIN 連結 `applications` (報名表) 和 `job` (活動表)
// 取得: 報名ID (a.id), 報名時間 (a.applied_at), 活動ID (j.postid), 活動名稱 (j.company), 內容 (j.content), 日期 (j.pdate)
// 篩選條件: a.user_account 必須為當前登入者
// 排序: 依照報名時間倒序 (越新的越上面)
$sql = "SELECT a.id AS app_id, a.applied_at, j.postid, j.company, j.content, j.pdate 
        FROM applications a 
        JOIN job j ON a.job_id = j.postid 
        WHERE a.user_account = '$account' 
        ORDER BY a.applied_at DESC";

// 執行查詢
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h2 class="mb-4">我的報名紀錄</h2>

    <!-- 顯示使用者資訊 -->
    <div class="alert alert-light border">
        使用者：<strong><?= htmlspecialchars($_SESSION['name']) ?></strong> (<?= htmlspecialchars($account) ?>)
    </div>

    <!-- 報名清單卡片 -->
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
                    // 檢查是否有查詢結果
                    if ($result && mysqli_num_rows($result) > 0) {
                        // 迴圈輸出每一筆報名資料
                        while ($row = mysqli_fetch_assoc($result)) {
                            // 格式化報名時間 (例如: 2023-10-27 10:30)
                            $apply_time = date('Y-m-d H:i', strtotime($row['applied_at']));
                            ?>
                            <tr>
                                <!-- 顯示報名時間 -->
                                <td class="text-muted small"><?= $apply_time ?></td>
                                <!-- 顯示主辦單位 (粗體) -->
                                <td class="fw-bold"><?= htmlspecialchars($row['company']) ?></td>
                                <!-- 顯示活動內容 -->
                                <td><?= htmlspecialchars($row['content']) ?></td>
                                <!-- 顯示活動舉辦日期 -->
                                <td><?= htmlspecialchars($row['pdate']) ?></td>
                                <td class="text-center">
                                    <!-- 取消報名按鈕 -->
                                    <!-- 連結到 cancel_application.php 並代入報名紀錄 ID (app_id) -->
                                    <!-- onclick 加入 JS 確認對話框，防止誤按 -->
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
                        // 若無資料，顯示提示訊息
                        echo '<tr><td colspan="5" class="text-center py-4 text-muted">目前還沒有報名任何活動喔！ <a href="index.php">去逛逛</a></td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 返回首頁按鈕 -->
    <div class="mt-3">
        <a href="index.php" class="btn btn-secondary">回首頁</a>
    </div>
</div>

<?php
// 關閉資料庫連線
mysqli_close($conn);
?>
<?php
// 引入頁尾
include "footer.php";
?>