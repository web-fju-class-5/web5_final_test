<?php
// index.php
// --------------------------------------------------------
// 這是系統的首頁，功能包含：
// 1. 顯示所有職缺/活動列表
// 2. 提供關鍵字、日期搜尋
// 3. 提供「多重標籤」篩選 (使用 OR 邏輯：符合任一標籤即顯示)
// 4. 提供「報名」按鈕連結到 apply.php
// --------------------------------------------------------

// 1. 啟動 Session (必須在所有 HTML 輸出之前)
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

// 3. 載入所有標籤 (Tags) 用於前端顯示 checkbox
$tags_by_type = [];
try {
    // SQL: 選取 id, name, type 並依照類型排序
    $tags_sql = "SELECT id, name, type FROM tags ORDER BY type, name";
    $tags_result = mysqli_query($conn, $tags_sql);

    if ($tags_result) {
        // 將結果整理成陣列，結構：$tags_by_type['技能類'] = [標籤1, 標籤2...]
        while ($tag_row = mysqli_fetch_assoc($tags_result)) {
            $tags_by_type[$tag_row['type']][] = $tag_row;
        }
    }
} catch (Exception $e) {
    // 忽略標籤讀取錯誤，不影響主頁面顯示
}

// 4. 接收並處理前端傳來的搜尋參數 (POST)
// 使用 ?? 運算子防止未定義錯誤
$selected_tags = $_POST['tags'] ?? []; // 使用者勾選的標籤陣列
$selected_tags_count = count($selected_tags);

$order = $_POST["order"] ?? ""; // 排序依據
// 防止 SQL Injection: 使用 mysqli_real_escape_string 處理文字輸入
$search_txt = mysqli_real_escape_string($conn, $_POST["searchtxt"] ?? "");
$date_start = $_POST["date_start"] ?? "";
$date_end = $_POST["date_end"] ?? "";

// 日期邏輯檢查：若起始日大於結束日，自動交換
if ($date_start && $date_end && $date_start > $date_end) {
    [$date_start, $date_end] = [$date_end, $date_start];
}

// 5. 建構 SQL 查詢語句 (核心邏輯)
$sql_select = "SELECT j.postid, j.company, j.content, j.pdate";
$sql_from = " FROM job j "; // 主表 job，別名 j
$sql_join = "";
$sql_group_by = "";
$where_conditions = []; // 存放所有 WHERE 條件

// --- 處理多標籤篩選 (OR 邏輯) ---
if ($selected_tags_count > 0) {
    // 若有勾選標籤，需 JOIN job_tags 表
    $sql_join = " JOIN job_tags jt ON j.postid = jt.job_id ";

    // 製作 IN 子句，例如: jt.tag_id IN (1, 3, 5)
    $in_clause = implode(',', array_map('intval', $selected_tags));
    $where_conditions[] = " jt.tag_id IN ($in_clause) ";

    // 為了避免同一職缺因符合多個標籤而重複出現，需使用 GROUP BY
    $sql_group_by = " GROUP BY j.postid ";
}

// --- 處理關鍵字搜尋 ---
if ($search_txt) {
    $where_conditions[] = " (j.company LIKE '%$search_txt%' OR j.content LIKE '%$search_txt%') ";
}
// --- 處理日期區間 ---
if ($date_start) {
    $where_conditions[] = " j.pdate >= '$date_start' ";
}
if ($date_end) {
    $where_conditions[] = " j.pdate <= '$date_end' ";
}

// 組合 WHERE 子句
$sql_where = "";
if (count($where_conditions) > 0) {
    $sql_where = " WHERE " . implode(' AND ', $where_conditions);
}

// 組合最終 SQL
$sql = $sql_select . $sql_from . $sql_join . $sql_where . $sql_group_by;

// --- 處理排序 ---
// 白名單檢查，防止 SQL 注入
if ($order && in_array($order, ['company', 'content', 'pdate'])) {
    $sql .= " ORDER BY j.$order ";
} else {
    // 預設排序：日期新到舊
    $sql .= " ORDER BY j.pdate DESC ";
}
?>

<div class="container mt-4">

    <!-- 歡迎訊息 -->
    <div class="alert alert-info py-2">
        你好，<strong><?= htmlspecialchars($_SESSION['name']) ?></strong>
    </div>

    <!-- 管理員按鈕：新增職缺 (權限檢查) -->
    <?php if (!empty($_SESSION['role']) && (strtoupper(trim($_SESSION['role'])) === 'M' || strtoupper(trim($_SESSION['role'])) === 'T')): ?>
        <a href="job_insert.php" class="btn btn-primary position-absolute"
            style="top: 5.5rem; right: 2rem; z-index: 10;">新增職缺</a>
    <?php endif; ?>

    <!-- 搜尋表單 -->
    <form method="POST" action="index.php" class="card card-body bg-light mb-4">
        <!-- 上半部：關鍵字、日期、排序 -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">關鍵字搜尋</label>
                <input placeholder="廠商或內容" value="<?= htmlspecialchars($search_txt) ?>" type="text" name="searchtxt"
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
                    <option value="" <?= ($order == "") ? 'selected' : '' ?>>預設 (日期最新)</option>
                    <option value="company" <?= ($order == "company") ? 'selected' : '' ?>>廠商</option>
                    <option value="content" <?= ($order == "content") ? 'selected' : '' ?>>內容</option>
                    <option value="pdate" <?= ($order == "pdate") ? 'selected' : '' ?>>日期</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <input class="btn btn-primary w-100" type="submit" value="搜尋">
            </div>
        </div>

        <!-- 下半部：標籤篩選 -->
        <hr>
        <label class="form-label fw-bold text-primary">標籤篩選 (勾選任一條件即可)</label>
        <div class="row g-3">
            <?php if (empty($tags_by_type)): ?>
                <div class="col-12 text-muted">目前無可用標籤。</div>
            <?php else: ?>
                <?php foreach ($tags_by_type as $type => $tags): ?>
                    <div class="col-md-4">
                        <h5><?= htmlspecialchars($type) ?></h5>
                        <div class="border rounded p-2 bg-white" style="max-height: 150px; overflow-y: auto;">
                            <?php foreach ($tags as $tag):
                                // 檢查是否已勾選 (保留狀態)
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
                        <th>主辦單位/廠商</th>
                        <th>活動</th>
                        <th>日期</th>
                        <th>功能</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // 執行 SQL
                        $result = mysqli_query($conn, $sql);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["company"]) ?></td>
                                    <td><?= htmlspecialchars($row["content"]) ?></td>
                                    <td><?= htmlspecialchars($row["pdate"]) ?></td>
                                    <td>
                                        <!-- 原有的修改/刪除按鈕 -->
                                        <a href="job_update.php?postid=<?= $row["postid"] ?>" class="btn btn-primary btn-sm">修改</a>
                                        <a href="job_delete.php?postid=<?= $row["postid"] ?>" class="btn btn-danger btn-sm">刪除</a>

                                        <!-- 報名按鈕：導向 apply.php -->
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

<?php mysqli_close($conn); ?>
<?php
include "footer.php";
?>