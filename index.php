<?php
// index.php
// 啟用 session，以便未來使用（例如登入）
session_start();

// 頁面標題
$title = "高中生活動搜尋";

// 引入頁首檔案
include "header.php";

// 引入資料庫連接檔案
try {
    require_once 'db.php'; // 假設 db.php 處理了 $conn 的連接
} catch (Exception $e) {
    echo "<div class='container alert alert-danger'>資料庫連接失敗: " . $e->getMessage() . "</div>";
    // 引入頁尾並結束
    // include "footer.php"; 
    exit;
}

// --- 1. 從資料庫獲取所有標籤，用於建立篩選器 ---

// $tags_by_type 是一個陣列，用來存放分類過的標籤
// 結構會像這樣：
// [
//    '學群' => [ ['id'=>1, 'name'=>'資訊'], ['id'=>2, 'name'=>'工程'] ],
//    '學類' => [ ['id'=>5, 'name'=>'工作坊'], ['id'=>6, 'name'=>'講座'] ]
// ]
$tags_by_type = [];
try {
    // 從 'tags' 資料表選擇所有標籤，並依照 'type' 和 'name' 排序
    $tags_sql = "SELECT id, name, type FROM tags ORDER BY type, name";
    $tags_result = mysqli_query($conn, $tags_sql);

    if (!$tags_result) {
        throw new Exception(mysqli_error($conn));
    }

    // 遍歷查詢結果，並將標籤存入 $tags_by_type 陣列
    while ($tag_row = mysqli_fetch_assoc($tags_result)) {
        // $tag_row['type'] (例如 '學群') 作為 key
        // $tag_row (例如 ['id'=>1, 'name'=>'資訊', 'type'=>'學群']) 作為 value 之一
        $tags_by_type[$tag_row['type']][] = $tag_row;
    }
} catch (Exception $e) {
    echo "<div class='container alert alert-danger'>讀取標籤失敗: " . $e->getMessage() . "</div>";
}


// --- 2. 處理表單提交的搜尋條件 ---

// 獲取使用者勾選的標籤 ID 陣列，如果沒有則為空陣列
// 表單中的 <input type='checkbox' name='tags[]'> 會將值組合成一個陣列
$selected_tags = $_POST['tags'] ?? [];
// 計算使用者勾選了幾個標籤
$selected_tags_count = count($selected_tags);

// 獲取文字搜尋的關鍵字，並進行跳脫字元處理，防止 SQL 注入
$search_txt = mysqli_real_escape_string($conn, $_POST['searchtxt'] ?? '');

// 獲取日期區間
$date_start = $_POST['date_start'] ?? '';
$date_end = $_POST['date_end'] ?? '';

// 如果起始日期 > 結束日期，則交換兩者
if ($date_start && $date_end && $date_start > $date_end) {
    [$date_start, $date_end] = [$date_end, $date_start]; // 陣列解構賦值
}

// --- 3. 根據搜尋條件建立 SQL 查詢 ---

// $where_conditions 陣列用來存放所有 WHERE 條件
$where_conditions = [];

// 建立 SQL 查詢的各個部分
// 基礎查詢：從 'events' 資料表選取活動資訊
$sql_select = "SELECT e.id, e.name, e.organizer_name, e.start_date, e.description";
$sql_from = " FROM events e ";
$sql_join = ""; // JOIN 預設為空
$sql_group_by = ""; // GROUP BY 預設為空
$sql_having = ""; // HAVING 預設為空

// ** 核心邏輯：處理多標籤搜尋 **
if ($selected_tags_count > 0) {
    // 如果使用者有選擇標籤
    
    // 1. JOIN：串聯 'event_tags' 中間表
    //    'events' (e) -> 'event_tags' (et)
    $sql_join = " JOIN event_tags et ON e.id = et.event_id ";

    // 2. WHERE：篩選出 'event_tags' 中 'tag_id' 欄位為使用者所選的任一標籤
    //    array_map('intval', ...) 確保陣列中所有值都是整數，防止 SQL 注入
    //    implode(',', ...) 將陣列 [1, 5, 10] 轉為字串 "1,5,10"
    $in_clause = implode(',', array_map('intval', $selected_tags));
    $where_conditions[] = " et.tag_id IN ($in_clause) ";

    // 3. GROUP BY：將結果依照 'event_id' 分組
    //    這樣才能計算每個活動命中了幾個標籤
    $sql_group_by = " GROUP BY e.id ";

    // 4. HAVING：這是 "AND" 條件的關鍵
    //    篩選出分組後，其「不重複的標籤ID (DISTINCT et.tag_id)」的「數量 (COUNT)」
    //    「等於 ( = )」使用者所勾選的標籤總數 ($selected_tags_count)
    //    範例：
    //    - 使用者選了 3 個標籤 (A, B, C)
    //    - 活動 1 只有標籤 A, B -> COUNT = 2，不等於 3，被過濾
    //    - 活動 2 有標籤 A, B, C -> COUNT = 3，等於 3，被選出
    //    - 活動 3 有標籤 A, B, C, D -> COUNT = 3 (因為 A,B,C 都在 IN (...) 範圍內)，等於 3，被選出
    $sql_having = " HAVING COUNT(DISTINCT et.tag_id) = $selected_tags_count ";
}

// 處理文字搜尋
if ($search_txt) {
    // 搜尋活動名稱、主辦單位、活動簡介
    $where_conditions[] = " (e.name LIKE '%$search_txt%' OR e.organizer_name LIKE '%$search_txt%' OR e.description LIKE '%$search_txt%') ";
}

// 處理日期搜尋 (假設活動日期在 'start_date' 欄位)
if ($date_start) {
    $where_conditions[] = " e.start_date >= '$date_start' ";
}
if ($date_end) {
    $where_conditions[] = " e.start_date <= '$date_end' ";
}

// 組合 WHERE 子句
$sql_where = "";
if (count($where_conditions) > 0) {
    // 使用 " AND " 將所有條件串聯起來
    $sql_where = " WHERE " . implode(' AND ', $where_conditions);
}

// 組合最終的 SQL 查詢字串
$sql = $sql_select . $sql_from . $sql_join . $sql_where . $sql_group_by . $sql_having;

// 加入排序：依照活動日期降冪排列 (最新的在最上面)
$sql .= " ORDER BY e.start_date DESC ";

// --- 4. 執行查詢並顯示結果 ---
?>

<!-- 主要內容容器 -->
<div class="container mt-4">
    <h2><?php echo $title; ?></h2>

    <!-- 搜尋表單 -->
    <!-- method="POST" 將表單資料送到目前頁面 (index.php) -->
    <form method="POST" action="index.php" class="card card-body bg-light mb-4">
        
        <!-- 第一行：關鍵字和日期 -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="searchtxt" class="form-label">關鍵字搜尋</label>
                <!-- htmlspecialchars() 防止 XSS 攻擊，並在刷新後保留使用者輸入 -->
                <input placeholder="搜尋活動名稱、主辦單位、簡介" value="<?= htmlspecialchars($search_txt) ?>" type="text" name="searchtxt" id="searchtxt" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="date_start" class="form-label">活動日期 (起)</label>
                <input type="date" name="date_start" id="date_start" class="form-control" value="<?= htmlspecialchars($date_start) ?>">
            </div>
            <div class="col-md-3">
                <label for="date_end" class="form-label">活動日期 (迄)</label>
                <input type="date" name="date_end" id="date_end" class="form-control" value="<?= htmlspecialchars($date_end) ?>">
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
            // 檢查是否有讀取到標籤
            if (empty($tags_by_type)) :
            ?>
                <div class="col-12">
                    <p class="text-muted">目前沒有可用的篩選標籤。</p>
                </div>
            <?php
            // 遍歷 $tags_by_type 陣列 (key 為 '學群', '學類'...)
            else :
                foreach ($tags_by_type as $type => $tags) :
            ?>
                    <div class="col-md-4">
                        <!-- 顯示標籤類型 (學群, 學類...) -->
                        <h5><?= htmlspecialchars($type) ?></h5>
                        <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                            <?php
                            // 遍歷該類型下的所有標籤
                            foreach ($tags as $tag) :
                                // 檢查這個標籤是否在使用者已勾選的陣列 $selected_tags 中
                                // in_array() 會回傳 true/false
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
                            <?php endforeach; // 結束 $tags 迴圈 ?>
                        </div>
                    </div>
            <?php
                endforeach; // 結束 $tags_by_type 迴圈
            endif; // 結束 if(empty)
            ?>
        </div>
    </form>


    <!-- 搜尋結果 -->
    <div class="card">
        <div class="card-header">
            搜尋結果
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mb-0" id="activity_table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 25%;">活動名稱</th>
                        <th style="width: 20%;">主辦單位</th>
                        <th style="width: 15%;">活動日期</th>
                        <th style="width: 40%;">活動簡介</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // 執行最終的 SQL 查詢
                        $result = mysqli_query($conn, $sql);

                        if (!$result) {
                            // 如果查詢失敗，拋出例外
                            throw new Exception("查詢執行失敗: " . mysqli_error($conn) . "<br><pre>SQL: $sql</pre>");
                        }

                        // 檢查是否有結果
                        if (mysqli_num_rows($result) > 0) {
                            // 遍歷結果並顯示
                            while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                                <tr>
                                    <!-- 顯示活動名稱 -->
                                    <td><?= htmlspecialchars($row["name"]) ?></td>
                                    <!-- 顯示主辦單位 -->
                                    <td><?= htmlspecialchars($row["organizer_name"]) ?></td>
                                    <!-- 顯示活動日期 -->
                                    <td><?= htmlspecialchars($row["start_date"]) ?></td>
                                    <!-- 顯示活動簡介 (只顯示前 100 個字) -->
                                    <td><?= htmlspecialchars(mb_substr($row["description"], 0, 100)) ?>...</td>
                                </tr>
                            <?php
                            } // 結束 while 迴圈
                        } else {
                            // 沒有找到任何結果
                            echo '<tr><td colspan="4" class="text-center">沒有找到符合條件的活動。</td></tr>';
                        }
                    } catch (Exception $e) {
                        // 捕捉並顯示 SQL 錯誤
                        echo '<tr><td colspan="4" class="text-center text-danger">查詢時發生錯誤: ' . $e->getMessage() . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// 引入頁尾檔案
// include "footer.php"; 

// 關閉資料庫連接
mysqli_close($conn);
?>