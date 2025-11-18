<?php
// index.php
// 升級為 'job' (求才資訊) 的多重標籤搜尋

session_start();
$title = "求才資訊 (多重標籤搜尋)";
include "header.php";

// 1. 連接資料庫
try {
    // db.php 會連接到 'practice' 資料庫
    require_once 'db.php'; 
} catch (Exception $e) {
    echo "<div class='container alert alert-danger'>資料庫連接失敗: " . $e->getMessage() . "</div>";
    exit;
}

// 2. 從資料庫獲取所有標籤 (用於篩選器)
$tags_by_type = [];
try {
    // 從 'tags' 資料表選擇所有標籤 (這張表由 add_tags_to_practice.sql 建立)
    $tags_sql = "SELECT id, name, type FROM tags ORDER BY type, name";
    $tags_result = mysqli_query($conn, $tags_sql);

    while ($tag_row = mysqli_fetch_assoc($tags_result)) {
        $tags_by_type[$tag_row['type']][] = $tag_row;
    }
} catch (Exception $e) {
    echo "<div class='container alert alert-danger'>讀取標籤失敗: " . $e->getMessage() . "</div>";
}


// 3. 處理表單提交的搜尋條件
// 獲取勾選的標籤
$selected_tags = $_POST['tags'] ?? [];
$selected_tags_count = count($selected_tags);

// 獲取原有的搜尋條件
$order = $_POST["order"] ?? ""; // 排序
$search_txt = mysqli_real_escape_string($conn, $_POST["searchtxt"] ?? ""); // 關鍵字
$date_start = $_POST["date_start"] ?? ""; // 日期起
$date_end = $_POST["date_end"] ?? ""; // 日期迄

// 日期區間相反時自動交換
if ($date_start && $date_end && $date_start > $date_end) {
    [$date_start, $date_end] = [$date_end, $date_start];
}

// 4. 根據搜尋條件建立 SQL 查詢
// 基礎查詢：從 'job' 資料表選取
$sql_select = "SELECT j.postid, j.company, j.content, j.pdate";
$sql_from = " FROM job j "; // 'j' 是 'job' 資料表的別名
$sql_join = "";
$sql_group_by = "";
$sql_having = "";

// 存放 WHERE 條件的陣列
$where_conditions = [];

// ** 核心邏輯：處理多標籤搜尋 **
if ($selected_tags_count > 0) {
    // 1. JOIN：串聯 'job_tags' 中間表
    $sql_join = " JOIN job_tags jt ON j.postid = jt.job_id "; // 'jt' 是 'job_tags' 的別名
    
    // 2. WHERE：篩選 'tag_id'
    $in_clause = implode(',', array_map('intval', $selected_tags));
    $where_conditions[] = " jt.tag_id IN ($in_clause) ";

    // 3. GROUP BY：依照 'postid' (job 的 ID) 分組
    $sql_group_by = " GROUP BY j.postid ";

    // 4. HAVING：實現 "AND" 條件
    $sql_having = " HAVING COUNT(DISTINCT jt.tag_id) = $selected_tags_count ";
}

// 處理原有的搜尋條件
if ($search_txt) {
    $where_conditions[] = " (j.company LIKE '%$search_txt%' OR j.content LIKE '%$search_txt%') ";
}
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

// 組合最終的 SQL 查詢字串
$sql = $sql_select . $sql_from . $sql_join . $sql_where . $sql_group_by . $sql_having;

// 處理排序 (注意：GROUP BY 後用 ORDER BY 欄位需要明確)
if ($order && in_array($order, ['company', 'content', 'pdate'])) {
    // 如果有 GROUP BY，ORDER BY 的欄位必須是 GROUP BY 的一部分或在彙總函式中
    // 為了安全，我們在 GROUP BY 之後，只允許對 SELECT 出來的欄位排序
    $sql .= " ORDER BY $order ";
} else {
    // 預設排序
    $sql .= " ORDER BY j.pdate DESC ";
}

?>

<!-- 主要內容容器 -->
<div class="container mt-4">

    <!-- 檢查是否有管理員權限 (來自 user 表) -->
    <?php if(!empty($_SESSION['role']) && (strtoupper(trim($_SESSION['role'])) === 'M' || strtoupper(trim($_SESSION['role'])) === 'T')): ?>
        <a href="job_insert.php" class="btn btn-primary position-absolute" style="top: 5.5rem; right: 2rem; z-index: 10;">新增職缺</a>
    <?php endif; ?>


    <!-- 搜尋表單 -->
    <form method="POST" action="index.php" class="card card-body bg-light mb-4">
        
        <!-- 第一行：原有的搜尋 -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">關鍵字搜尋</label>
                <input placeholder="搜尋廠商及內容" value="<?=htmlspecialchars($search_txt)?>" type="text" name="searchtxt" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">刊登日期 (起)</label>
                <input type="date" name="date_start" class="form-control" value="<?=htmlspecialchars($date_start)?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">刊登日期 (迄)</label>
                <input type="date" name="date_end" class="form-control" value="<?=htmlspecialchars($date_end)?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">排序方式</label>
                <select name="order" class="form-select">
                    <option value="" <?=($order=="")?'selected':''?>>預設 (日期最新)</option>
                    <option value="company" <?=($order=="company")?'selected':''?>>求才廠商</option>
                    <option value="content" <?=($order=="content")?'selected':''?>>求才內容</option>
                    <option value="pdate" <?=($order=="pdate")?'selected':''?>>刊登日期</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <input class="btn btn-primary w-100" type="submit" value="搜尋">
            </div>
        </div>

        <!-- 第二行：多標籤篩選 -->
        <hr>
        <label class="form-label fw-bold">標籤篩選 (勾選的條件將 '同時' 成立)</label>
        <div class="row g-3">
            <?php
            if (empty($tags_by_type)) :
                echo '<p class="text-muted">目前沒有可用的篩選標籤。</p>';
            else :
                // 遍歷所有標籤類型 (例如 '技能', '經驗要求')
                foreach ($tags_by_type as $type => $tags) :
            ?>
                    <div class="col-md-4">
                        <h5><?= htmlspecialchars($type) ?></h5>
                        <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                            <?php
                            // 遍歷該類型下的所有標籤
                            foreach ($tags as $tag) :
                                $is_checked = in_array($tag['id'], $selected_tags);
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="tags[]" 
                                           value="<?= $tag['id'] ?>" 
                                           id="tag_<?= $tag['id'] ?>"
                                           <?= $is_checked ? 'checked' : '' ?>
                                    >
                                    <label class="form-check-label" for="tag_<?= $tag['id'] ?>">
                                        <?= htmlspecialchars($tag['name']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
            <?php
                endforeach; 
            endif;
            ?>
        </div>
    </form>


    <!-- 搜尋結果 -->
    <div class="card">
        <div class="card-header">
            搜尋結果
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0" id="job_table">
                <thead class="table-light">
                    <tr>
                        <th>求才廠商</th>
                        <th>求才內容</th>
                        <th>日期</th>
                        <th>編輯</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $result = mysqli_query($conn, $sql);
                        if (!$result) {
                            throw new Exception("查詢執行失敗: " . mysqli_error($conn) . "<br><pre>SQL: $sql</pre>");
                        }

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["company"]) ?></td>
                                    <td><?= htmlspecialchars($row["content"]) ?></td>
                                    <td><?= htmlspecialchars($row["pdate"]) ?></td>
                                    <td>
                                        <!-- 修改刪除按鈕 (您可以加上權限判斷) -->
                                        <a href="job_update.php?postid=<?= $row["postid"] ?>" class="btn btn-primary btn-sm">修改</a>
                                        <a href="job_delete.php?postid=<?= $row["postid"] ?>" class="btn btn-danger btn-sm">刪除</a>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            echo '<tr><td colspan="4" class="text-center">沒有找到符合條件的職缺。</td></tr>';
                        }
                    } catch (Exception $e) {
                        echo '<tr><td colspan="4" class="text-center text-danger">查詢時發生錯誤: ' . $e->getMessage() . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
// include "footer.php"; 
?>