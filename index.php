<?php
// index.php
// --------------------------------------------------------
// 系統首頁 (Home Page)
// 功能總覽：
// 1. 活動列表展示
// 2. 條件搜尋 (關鍵字、日期)
// 3. 標籤篩選 (Tag Filtering)
// 4. 管理員操作入口 (新增活動)
// 5. 報名入口
// 6. 統計圖表展示 (Chart.js)
// --------------------------------------------------------

// 1. 啟動 Session
session_start();

$title = "活動搜尋";
include "header.php"; // 引入頁首

// 2. 連接資料庫
try {
    require_once 'db.php';
} catch (Exception $e) {
    echo "<div class='container alert alert-danger'>資料庫連接失敗: " . $e->getMessage() . "</div>";
    exit;
}

// 3. 載入標籤 (Tags) 資料
// 用於在頁面上生成篩選的 Checkbox
$tags_by_type = [];
try {
    // 查詢所有標籤，並按類型排序
    $tags_sql = "SELECT id, name, type FROM tags ORDER BY type, name";
    $tags_result = mysqli_query($conn, $tags_sql);

    if ($tags_result) {
        // 將標籤依 Type 分組整理
        // $tags_by_type['技能類'] = [tag1, tag2...]
        while ($tag_row = mysqli_fetch_assoc($tags_result)) {
            $tags_by_type[$tag_row['type']][] = $tag_row;
        }
    }
} catch (Exception $e) {
    // 若標籤讀取失敗，僅忽略不顯示，不影響主要列表
}

// 4. 接收搜尋參數 (POST)
// 使用 $_POST['key'] ?? default 語法確保變數存在
$selected_tags = $_POST['tags'] ?? []; // 使用者目前勾選的標籤 ID 陣列
$selected_tags_count = count($selected_tags);

$order = $_POST["order"] ?? ""; // 排序欄位
// 搜尋關鍵字 (防止 SQL Injection)
$search_txt = mysqli_real_escape_string($conn, $_POST["searchtxt"] ?? "");
$date_start = $_POST["date_start"] ?? ""; // 起始日
$date_end = $_POST["date_end"] ?? "";     // 結束日

// 日期區間智慧調整：若「起始 > 結束」，自動交換
if ($date_start && $date_end && $date_start > $date_end) {
    [$date_start, $date_end] = [$date_end, $date_start];
}

// 5. 動態構建 SQL 查詢語句
$sql_select = "SELECT j.postid, j.company, j.content, j.pdate";
$sql_from = " FROM job j "; // 別名 j 代表 job 表
$sql_join = "";
$sql_group_by = "";
$where_conditions = []; // 用來收集所有的 WHERE 條件字串

// --- 5-1. 多標籤篩選邏輯 (OR) ---
// 若使用者有勾選標籤
if ($selected_tags_count > 0) {
    // Join job_tags 關聯表
    $sql_join = " JOIN job_tags jt ON j.postid = jt.job_id ";

    // 製作 IN (1, 2, 3) 字句
    // array_map('intval') 確保都是整數，避免注入
    $in_clause = implode(',', array_map('intval', $selected_tags));
    $where_conditions[] = " jt.tag_id IN ($in_clause) ";

    // 因為 JOIN 可能導致同一職缺出現多次 (對應多個 Tag)，所以需要 GROUP BY 去重
    $sql_group_by = " GROUP BY j.postid ";
}

// --- 5-2. 關鍵字搜尋 ---
if ($search_txt) {
    // 搜尋 company 或 content 欄位
    $where_conditions[] = " (j.company LIKE '%$search_txt%' OR j.content LIKE '%$search_txt%') ";
}

// --- 5-3. 日期篩選 ---
if ($date_start) {
    $where_conditions[] = " j.pdate >= '$date_start' ";
}
if ($date_end) {
    $where_conditions[] = " j.pdate <= '$date_end' ";
}

// --- 5-4. 組合 WHERE 子句 ---
$sql_where = "";
if (count($where_conditions) > 0) {
    $sql_where = " WHERE " . implode(' AND ', $where_conditions);
}

// 組合完整 SQL
$sql = $sql_select . $sql_from . $sql_join . $sql_where . $sql_group_by;

// --- 5-5. 排序邏輯 ---
// 白名單檢查：只允許特定的排序欄位，防止惡意輸入
if ($order && in_array($order, ['company', 'content', 'pdate'])) {
    $sql .= " ORDER BY j.$order ";
} else {
    // 預設排序：依日期最新優先 (DESC)
    $sql .= " ORDER BY j.pdate DESC ";
}
?>

<div class="container mt-4">

    <!-- 歡迎區塊 -->
    <div class="alert alert-info py-2">
        你好，<strong><?= htmlspecialchars($_SESSION['name']) ?></strong>
    </div>

    <!-- 管理功能：新增活動按鈕 -->
    <!-- 只有 Role = M (Manager) 或 T (Teacher) 可見 -->
    <?php if (!empty($_SESSION['role']) && (strtoupper(trim($_SESSION['role'])) === 'M' || strtoupper(trim($_SESSION['role'])) === 'T')): ?>
        <a href="job_insert.php" class="btn btn-primary position-absolute"
            style="top: 5.5rem; right: 2rem; z-index: 10;">新增活動</a>
    <?php endif; ?>

    <!-- 搜尋與篩選表單 -->
    <form method="POST" action="index.php" class="card card-body bg-light mb-4">
        <!-- 上半部搜尋列 -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">關鍵字搜尋</label>
                <input placeholder="主辦單位或內容" value="<?= htmlspecialchars($search_txt) ?>" type="text" name="searchtxt"
                    class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">日期 (起)</label>
                <input type="date" name="date_start" class="form-control" value="<?= htmlspecialchars($date_start) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">日期 (迄)</label>
                <input type="date" name="date_end" class="form-control" value="<?= htmlspecialchars($date_end) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">排序方式</label>
                <select name="order" class="form-select">
                    <!-- 判斷目前選中的項目並加上 selected 屬性 -->
                    <option value="" <?= ($order == "") ? 'selected' : '' ?>>預設 (日期最新)</option>
                    <option value="company" <?= ($order == "company") ? 'selected' : '' ?>>主辦單位</option>
                    <option value="content" <?= ($order == "content") ? 'selected' : '' ?>>內容</option>
                    <option value="pdate" <?= ($order == "pdate") ? 'selected' : '' ?>>日期</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <input class="btn btn-primary w-100" type="submit" value="搜尋">
            </div>
        </div>

        <!-- 下半部標籤區域 -->
        <hr>
        <label class="form-label fw-bold text-primary">標籤篩選 (勾選任一條件即可)</label>
        <div class="row g-3">
            <?php if (empty($tags_by_type)): ?>
                <div class="col-12 text-muted">目前無可用標籤。</div>
            <?php else: ?>
                <!-- 迴圈顯示各類標籤 -->
                <?php foreach ($tags_by_type as $type => $tags): ?>
                    <div class="col-md-4">
                        <h5><?= htmlspecialchars($type) ?></h5>
                        <div class="border rounded p-2 bg-white" style="max-height: 150px; overflow-y: auto;">
                            <?php foreach ($tags as $tag):
                                // 判斷標籤是否被選中
                                $is_checked = in_array($tag['id'], $selected_tags);
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tags[]" value="<?= $tag['id'] ?>"
                                        id="tag_<?= $tag['id'] ?>" <?= $is_checked ? 'checked' : '' ?>>
                                    <label class="form-check-label"
                                        for="tag_<?= $tag['id'] ?>"><?= htmlspecialchars($tag['name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </form>

    <!-- 搜尋結果列表 -->
    <div class="card">
        <div class="card-header">搜尋結果</div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>主辦單位</th>
                        <th>活動名稱</th>
                        <th>日期</th>
                        <th>功能</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // 執行 SQL 查詢
                        $result = mysqli_query($conn, $sql);

                        // 判斷有無資料
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["company"]) ?></td>
                                    <td><?= htmlspecialchars($row["content"]) ?></td>
                                    <td><?= htmlspecialchars($row["pdate"]) ?></td>
                                    <td>
                                        <!-- 修改/刪除按鈕 (應視權限隱藏，不過原程式碼似乎公開) -->
                                        <a href="job_update.php?postid=<?= $row["postid"] ?>" class="btn btn-primary btn-sm">修改</a>
                                        <a href="job_delete.php?postid=<?= $row["postid"] ?>" class="btn btn-danger btn-sm">刪除</a>

                                        <!-- 報名按鈕 -->
                                        <a href="apply.php?postid=<?= $row["postid"] ?>" class="btn btn-success btn-sm ms-2"
                                            onclick="return confirm('確定要報名 <?= htmlspecialchars($row['company']) ?> 的活動嗎？');">
                                            報名
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="4" class="text-center">沒有符合的資料。</td></tr>';
                        }
                    } catch (Exception $e) {
                        echo '<tr><td colspan="4" class="text-center text-danger">錯誤: ' . $e->getMessage() . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 統計圖表區塊 -->
<div class="mt-4" style="height: 400px;">
    <h3>報名人數統計圖</h3>
    <canvas id="myChart"></canvas>
</div>

<!-- 引入 Chart.js 函式庫 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 等待 DOM 載入完成
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('myChart');

        // 透過 fetch API 從 chart_data.php 取得 JSON 資料
        fetch('chart_data.php')
            .then(response => response.json()) // 解析 JSON
            .then(json => {
                // 初始化 Chart.js
                new Chart(ctx, {
                    type: 'bar', // 圖表類型：長條圖
                    data: {
                        labels: json.labels, // X 軸標籤 (活動名稱)
                        datasets: [{
                            label: '# 報名人數',
                            data: json.data, // Y 軸數據 (人數)
                            backgroundColor: 'rgba(54, 162, 235, 0.5)', // 長條顏色
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            barThickness: 40,
                            maxBarThickness: 50
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: '活動名稱',
                                    color: '#000',
                                    font: { size: 16, weight: 'bold' }
                                }
                            },
                            y: {
                                beginAtZero: true, // Y 軸從 0 開始
                                title: {
                                    display: true,
                                    text: '報名人數',
                                    font: { size: 16, weight: 'bold' }
                                },
                                ticks: { precision: 0, stepSize: 1 } // 設定刻度為整數
                            }
                        }
                    }
                });
            })
            .catch(err => console.error('抓取報名統計資料失敗:', err));
    });
</script>

<br><br><br>
</div>

<?php
// 關閉資料庫連線並引入頁尾
mysqli_close($conn);
include "footer.php";
?>