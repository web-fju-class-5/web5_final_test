<?php
// index.php
// 這是求才資訊列表頁面，加入了多重標籤 (Tag) 篩選功能。
// 篩選邏輯採用 "OR"：只要職缺符合任一被勾選的標籤，就會被選出。

// 啟動 Session，通常用於管理員登入狀態判斷
session_start();

// 設定頁面標題
$title = "求才資訊 (多重標籤篩選)";

// 引入頁首檔案，通常包含 HTML 的 <head> 和導覽列 (Navbar)
include "header.php";

// --- 1. 資料庫連接與標籤資料載入 ---

// 引入資料庫連接檔案 (db.php 應連接到 'practice' 資料庫)
try {
    require_once 'db.php'; 
} catch (Exception $e) {
    // 若連接失敗，顯示錯誤並終止腳本
    echo "<div class='container alert alert-danger'>資料庫連接失敗: " . $e->getMessage() . "</div>";
    exit;
}

// 從資料庫獲取所有標籤，用於在表單中顯示多選框
$tags_by_type = [];
try {
    // 查詢 tags 資料表，按類型和名稱排序
    $tags_sql = "SELECT id, name, type FROM tags ORDER BY type, name";
    $tags_result = mysqli_query($conn, $tags_sql);

    // 將結果依照 'type' (例如：技能、經驗要求) 分組存入陣列
    while ($tag_row = mysqli_fetch_assoc($tags_result)) {
        $tags_by_type[$tag_row['type']][] = $tag_row;
    }
} catch (Exception $e) {
    // 如果查詢 tags 失敗 (例如 tags 表尚未建立)，忽略錯誤，確保頁面仍能載入
}


// --- 2. 處理表單提交的搜尋條件 ---

// 獲取使用者勾選的所有標籤 ID
$selected_tags = $_POST['tags'] ?? [];
$selected_tags_count = count($selected_tags);

// 獲取排序欄位，若無則為空字串
$order = $_POST["order"] ?? "";

// 獲取關鍵字，並使用 mysqli_real_escape_string 防止 SQL 注入
$search_txt = mysqli_real_escape_string($conn, $_POST["searchtxt"] ?? "");

// 獲取日期區間
$date_start = $_POST["date_start"] ?? "";
$date_end = $_POST["date_end"] ?? "";

// 檢查日期區間是否相反，若相反則自動交換，避免 SQL 錯誤
if ($date_start && $date_end && $date_start > $date_end) {
    [$date_start, $date_end] = [$date_end, $date_start];
}


// --- 3. 建立動態 SQL 查詢語句 ---

// 選擇欄位：j.* 是 'job' 表格的別名
$sql_select = "SELECT j.postid, j.company, j.content, j.pdate";
$sql_from = " FROM job j "; // 設定主表 job，別名為 j
$sql_join = "";
$sql_group_by = "";

// $where_conditions 陣列用來存放所有 WHERE 條件 (關鍵字、日期、標籤)
$where_conditions = [];

// ** 處理多標籤篩選 (OR 邏輯) **
if ($selected_tags_count > 0) {
    // 1. JOIN：需要連接 job_tags 中間表
    $sql_join = " JOIN job_tags jt ON j.postid = jt.job_id ";
    
    // 2. WHERE IN：建立 IN 語句，找出 job_tags 中包含任一被勾選 tag_id 的職缺
    // implode(','...) 將陣列 [1, 5, 8] 轉為字串 "1,5,8"
    $in_clause = implode(',', array_map('intval', $selected_tags));
    $where_conditions[] = " jt.tag_id IN ($in_clause) ";

    // 3. GROUP BY：將結果依照 job.postid 分組
    // 這是為了去除重複 (因為一個職缺如果符合多個標籤，JOIN 會產生多列)
    $sql_group_by = " GROUP BY j.postid ";
    
    // ** 這裡不需要 HAVING 子句，因為是 OR 邏輯 **
}

// 處理關鍵字搜尋 (包含公司名稱 company 或職缺內容 content)
if ($search_txt) {
    $where_conditions[] = " (j.company LIKE '%$search_txt%' OR j.content LIKE '%$search_txt%') ";
}

// 處理日期起始條件
if ($date_start) {
    $where_conditions[] = " j.pdate >= '$date_start' ";
}

// 處理日期結束條件
if ($date_end) {
    $where_conditions[] = " j.pdate <= '$date_end' ";
}

// 組合 WHERE 子句：將所有條件用 " AND " 串聯起來
$sql_where = "";
if (count($where_conditions) > 0) {
    $sql_where = " WHERE " . implode(' AND ', $where_conditions);
}

// 組合最終的 SQL 查詢字串
$sql = $sql_select . $sql_from . $sql_join . $sql_where . $sql_group_by;

// 處理排序
if ($order && in_array($order, ['company', 'content', 'pdate'])) {
    // 確保排序欄位有效
    $sql .= " ORDER BY j.$order ";
} else {
    // 預設依照日期降冪排序 (最新在前)
    $sql .= " ORDER BY j.pdate DESC ";
}
?>

<!-- --- 4. 網頁 HTML 結構與表單顯示 --- -->
<div class="container mt-4">
    
    <!-- 判斷是否有管理員/老師權限，顯示新增職缺按鈕 -->
    <?php if(!empty($_SESSION['role']) && (strtoupper(trim($_SESSION['role'])) === 'M' || strtoupper(trim($_SESSION['role'])) === 'T')): ?>
        <a href="job_insert.php" class="btn btn-primary position-absolute" style="top: 5.5rem; right: 2rem; z-index: 10;">新增職缺</a>
    <?php endif; ?>

    <!-- 搜尋表單 (POST 方式提交到本頁面 index.php) -->
    <form method="POST" action="index.php" class="card card-body bg-light mb-4">
        
        <!-- 一般搜尋條件區塊 -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">關鍵字搜尋</label>
                <!-- 顯示上次輸入的值 -->
                <input placeholder="廠商或內容" value="<?=htmlspecialchars($search_txt)?>" type="text" name="searchtxt" class="form-control">
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
                    <option value="company" <?=($order=="company")?'selected':''?>>廠商</option>
                    <option value="content" <?=($order=="content")?'selected':''?>>內容</option>
                    <option value="pdate" <?=($order=="pdate")?'selected':''?>>日期</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <input class="btn btn-primary w-100" type="submit" value="搜尋">
            </div>
        </div>

        <!-- 多標籤篩選區塊 -->
        <hr>
        <label class="form-label fw-bold text-primary">標籤篩選 (勾選任一條件即可 / OR 邏輯)</label>
        <div class="row g-3">
            <?php
            // 檢查是否有標籤資料
            if (empty($tags_by_type)) :
                echo '<div class="col-12 text-muted">目前資料庫中沒有設定標籤 (Tags)。請先執行 practice_complete.sql。</div>';
            else :
                // 遍歷所有標籤類型 (例如 '技能', '經驗要求')
                foreach ($tags_by_type as $type => $tags) :
            ?>
                    <div class="col-md-4">
                        <h5><?= htmlspecialchars($type) ?></h5>
                        <div class="border rounded p-2 bg-white" style="max-height: 150px; overflow-y: auto;">
                            <?php
                            // 遍歷該類型下的所有標籤
                            foreach ($tags as $tag) :
                                // 檢查該標籤是否在上次提交時被勾選
                                $is_checked = in_array($tag['id'], $selected_tags);
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="tags[]" // 必須使用陣列名稱，才能傳送多個勾選值
                                           value="<?= $tag['id'] ?>" 
                                           id="tag_<?= $tag['id'] ?>"
                                           <?= $is_checked ? 'checked' : '' ?> // 如果被勾選，則加上 checked 屬性
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


    <!-- --- 5. 搜尋結果顯示 --- -->
    <div class="card">
        <div class="card-header">
            搜尋結果
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 25%;">求才廠商</th>
                        <th style="width: 45%;">求才內容</th>
                        <th style="width: 15%;">日期</th>
                        <th style="width: 15%;">編輯</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // 執行最終組裝好的 SQL 查詢
                        $result = mysqli_query($conn, $sql);

                        // 檢查查詢是否成功
                        if (!$result) throw new Exception(mysqli_error($conn));

                        // 判斷是否有資料
                        if (mysqli_num_rows($result) > 0) {
                            // 遍歷所有查詢結果並顯示
                            while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["company"]) ?></td>
                                    <td><?= htmlspecialchars($row["content"]) ?></td>
                                    <td><?= htmlspecialchars($row["pdate"]) ?></td>
                                    <td>
                                        <!-- 提供修改和刪除的連結，帶上 postid 作為參數 -->
                                        <a href="job_update.php?postid=<?= $row["postid"] ?>" class="btn btn-primary btn-sm">修改</a>
                                        <a href="job_delete.php?postid=<?= $row["postid"] ?>" class="btn btn-danger btn-sm">刪除</a>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            // 沒有找到符合條件的資料
                            echo '<tr><td colspan="4" class="text-center">沒有找到符合條件的職缺。</td></tr>';
                        }
                    } catch (Exception $e) {
                        // 顯示執行查詢時發生的錯誤，方便除錯
                        echo '<tr><td colspan="4" class="text-center text-danger">查詢時發生錯誤: ' . $e->getMessage() . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
// 關閉資料庫連接
mysqli_close($conn); 
// 引入頁尾檔案 (假設有 footer.php)
// include "footer.php"; 
?>