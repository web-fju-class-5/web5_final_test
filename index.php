<?php
// index.php
// 這是系統的首頁，功能包含：
// 1. 顯示所有職缺/活動列表
// 2. 提供關鍵字、日期搜尋
// 3. 提供「多重標籤」篩選 (使用 OR 邏輯)
// 4. 提供「報名」按鈕

// 啟動 Session，必須在所有 HTML 輸出之前執行
session_start();

// --- [測試用] 模擬自動登入 ---
// 在正式環境中，這段應該被移除，改為製作獨立的 login.php
// 這裡預設使用者為 'user1'，角色為 'S' (學生)
if (!isset($_SESSION['account'])) {
    $_SESSION['account'] = 'user1'; 
    $_SESSION['role'] = 'S';       
    $_SESSION['name'] = '小明';
}
// ---------------------------

// 設定頁面標題，header.php 會用到
$title = "求才資訊 (多重標籤篩選)";
include "header.php";

// 1. 資料庫連接
try {
    require_once 'db.php'; 
} catch (Exception $e) {
    // 如果連接失敗，顯示錯誤訊息並停止
    echo "<div class='container alert alert-danger'>資料庫連接失敗: " . $e->getMessage() . "</div>";
    exit;
}

// 2. 載入所有標籤 (Tags) 用於前端顯示
// 我們將標籤依照 'type' (例如：技能、經驗要求) 分組
$tags_by_type = [];
try {
    $tags_sql = "SELECT id, name, type FROM tags ORDER BY type, name";
    $tags_result = mysqli_query($conn, $tags_sql);
    
    if ($tags_result) {
        while ($tag_row = mysqli_fetch_assoc($tags_result)) {
            // 將查詢結果存入二維陣列：$tags_by_type['技能'][0] = ...
            $tags_by_type[$tag_row['type']][] = $tag_row;
        }
    }
} catch (Exception $e) {
    // 如果 tags 資料表不存在或查詢錯誤，暫時忽略，不影響主功能
}

// 3. 接收並處理前端傳來的搜尋參數 (POST)
// 使用 ?? 運算子：如果 $_POST['tags'] 不存在，則預設為空陣列 []
$selected_tags = $_POST['tags'] ?? [];
$selected_tags_count = count($selected_tags); // 計算勾選了幾個標籤

$order = $_POST["order"] ?? ""; // 排序欄位
// mysqli_real_escape_string 用於處理特殊字元，防止 SQL Injection 攻擊
$search_txt = mysqli_real_escape_string($conn, $_POST["searchtxt"] ?? ""); 
$date_start = $_POST["date_start"] ?? "";
$date_end = $_POST["date_end"] ?? "";

// 邏輯檢查：如果起始日期大於結束日期，自動交換兩者
if ($date_start && $date_end && $date_start > $date_end) {
    [$date_start, $date_end] = [$date_end, $date_start];
}

// 4. 建構 SQL 查詢語句 (核心邏輯)
// 基礎 SELECT
$sql_select = "SELECT j.postid, j.company, j.content, j.pdate";
$sql_from = " FROM job j "; // 主表 job，別名 j
$sql_join = "";
$sql_group_by = "";
$where_conditions = []; // 用來存放所有的 WHERE 子句

// --- 處理多標籤篩選 (OR 邏輯) ---
if ($selected_tags_count > 0) {
    // 如果有勾選標籤，必須 JOIN job_tags 中間表
    $sql_join = " JOIN job_tags jt ON j.postid = jt.job_id ";
    
    // 製作 IN 子句，例如：jt.tag_id IN (1, 3, 5)
    // array_map('intval') 確保所有值都是整數，安全性處理
    $in_clause = implode(',', array_map('intval', $selected_tags));
    $where_conditions[] = " jt.tag_id IN ($in_clause) ";
    
    // 因為一個職缺可能符合多個標籤，JOIN 後會出現多筆重複資料
    // 使用 GROUP BY j.postid 來將重複的職缺合併為一筆
    $sql_group_by = " GROUP BY j.postid ";
}

// --- 處理關鍵字搜尋 ---
if ($search_txt) {
    // 搜尋公司名稱 OR 內容
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
    // 用 AND 連接所有條件：(標籤符合) AND (關鍵字符合) AND (日期符合)
    $sql_where = " WHERE " . implode(' AND ', $where_conditions);
}

// 組合最終 SQL
$sql = $sql_select . $sql_from . $sql_join . $sql_where . $sql_group_by;

// --- 處理排序 ---
// 只有在允許的欄位清單中才進行排序，防止 SQL 錯誤
if ($order && in_array($order, ['company', 'content', 'pdate'])) {
    $sql .= " ORDER BY j.$order ";
} else {
    // 預設：依照日期降冪 (新的在前)
    $sql .= " ORDER BY j.pdate DESC ";
}
?>

<div class="container mt-4">
    
    <!-- 顯示歡迎訊息 (來自 Session) -->
    <div class="alert alert-info py-2">
        你好，<strong><?= htmlspecialchars($_SESSION['name']) ?></strong> (<?= htmlspecialchars($_SESSION['account']) ?>)
    </div>

    <!-- 管理員/老師專屬功能：新增職缺按鈕 -->
    <?php if(!empty($_SESSION['role']) && (strtoupper(trim($_SESSION['role'])) === 'M' || strtoupper(trim($_SESSION['role'])) === 'T')): ?>
        <a href="job_insert.php" class="btn btn-primary position-absolute" style="top: 5.5rem; right: 2rem; z-index: 10;">新增職缺</a>
    <?php endif; ?>

    <!-- 搜尋表單開始 -->
    <form method="POST" action="index.php" class="card card-body bg-light mb-4">
        <!-- 上半部：關鍵字、日期、排序 -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">關鍵字搜尋</label>
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

        <!-- 下半部：標籤篩選區 -->
        <hr>
        <label class="form-label fw-bold text-primary">標籤篩選 (勾選任一條件即可 / OR 邏輯)</label>
        <div class="row g-3">
            <?php if (empty($tags_by_type)) : ?>
                <div class="col-12 text-muted">目前資料庫中沒有設定標籤。</div>
            <?php else : ?>
                <!-- 迴圈輸出標籤類型 -->
                <?php foreach ($tags_by_type as $type => $tags) : ?>
                    <div class="col-md-4">
                        <h5><?= htmlspecialchars($type) ?></h5>
                        <div class="border rounded p-2 bg-white" style="max-height: 150px; overflow-y: auto;">
                            <!-- 迴圈輸出該類型下的每個標籤 -->
                            <?php foreach ($tags as $tag) : 
                                // 檢查此標籤是否在上次提交時被勾選 (保持勾選狀態)
                                $is_checked = in_array($tag['id'], $selected_tags);
                            ?>
                                <div class="form-check">
                                    <!-- name="tags[]" 表示這是一個陣列，可以傳送多個值 -->
                                    <input class="form-check-input" type="checkbox" name="tags[]" value="<?= $tag['id'] ?>" id="tag_<?= $tag['id'] ?>" <?= $is_checked ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="tag_<?= $tag['id'] ?>"><?= htmlspecialchars($tag['name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </form>


    <!-- 搜尋結果顯示區 -->
    <div class="card">
        <div class="card-header">搜尋結果</div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>求才廠商</th>
                        <th>求才內容</th>
                        <th>日期</th>
                        <th>編輯 / 報名</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // 執行 SQL 查詢
                        $result = mysqli_query($conn, $sql);
                        
                        // 檢查是否有資料
                        if ($result && mysqli_num_rows($result) > 0) {
                            // 迴圈取每一筆資料
                            while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                                <tr>
                                    <td><?= htmlspecialchars($row["company"]) ?></td>
                                    <td><?= htmlspecialchars($row["content"]) ?></td>
                                    <td><?= htmlspecialchars($row["pdate"]) ?></td>
                                    <td>
                                        <!-- 修改與刪除按鈕 (原功能) -->
                                        <a href="job_update.php?postid=<?= $row["postid"] ?>" class="btn btn-primary btn-sm">修改</a>
                                        <a href="job_delete.php?postid=<?= $row["postid"] ?>" class="btn btn-danger btn-sm">刪除</a>
                                        
                                        <!-- 新增：報名按鈕 -->
                                        <!-- 1. 連結指向 apply.php -->
                                        <!-- 2. 透過 GET 參數傳遞 postid -->
                                        <!-- 3. onclick 事件增加確認視窗，避免誤按 -->
                                        <a href="apply.php?postid=<?= $row["postid"] ?>" 
                                           class="btn btn-success btn-sm ms-2"
                                           onclick="return confirm('確定要報名 <?= htmlspecialchars($row['company']) ?> 的活動嗎？');">
                                           報名
                                        </a>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else {
                            echo '<tr><td colspan="4" class="text-center">沒有資料。</td></tr>';
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
<?php include "footer.php"; // 引用頁尾 (如果有的話) ?>