<?php
// job_update.php
// --------------------------------------------------------
// 用途：修改職缺/活動資料
// --------------------------------------------------------

// 引入 header 與 db 元件
require_once "header.php";
require_once "db.php";

// 身份驗證檢查
// 只有角色為 'M' (Manager) 的管理員可以執行修改
// 若 session 中沒有 role 或 role 不是 'M'，則顯示訊息並停止執行
if (empty($_SESSION['role']) || strtoupper(trim($_SESSION['role'])) !== 'M') {
    die("只有管理員可以修改資料");
}

// 取得要修改的活動 ID (postid)
// 可以從網址參數 $_GET 拿，也可以從表單送出的 $_POST 拿 (順序：先 GET 再 POST，或使用 null 合併)
$postid = $_GET['postid'] ?? $_POST['postid'] ?? null;

// 如果沒有 ID，無法知道要改哪一筆，故終止程式
if (!$postid) {
    die("缺少資料ID");
}

$msg = ""; // 用來存放提示訊息

// 1. 處理資料更新 (當發生 POST 請求時)
if ($_POST) {
    // 接收表單資料
    // company: 公司名稱/主辦單位
    // content: 活動內容
    $company = $_POST["company"] ?? "";
    $content = $_POST["content"] ?? "";

    // 資料安全過濾 (SQL Injection 防護)
    $company_safe = mysqli_real_escape_string($conn, $company);
    $content_safe = mysqli_real_escape_string($conn, $content);

    // 準備 UPDATE SQL 語法
    // 把 postid 符合的那一筆資料的 company 和 content 更新
    $sql = "UPDATE job SET company = ?, content = ? WHERE postid = ?";

    // 初始化 Statement
    $stmt = mysqli_stmt_init($conn);

    // 預備 SQL
    if (mysqli_stmt_prepare($stmt, $sql)) {
        // 綁定參數：兩個字串 (ss) 與一個整數 (i)
        // 順序與 SQL 中的 ? 對應
        mysqli_stmt_bind_param($stmt, "ssi", $company_safe, $content_safe, $postid);

        // 執行更新
        if (mysqli_stmt_execute($stmt)) {
            // 關閉 Statement 與 DB 連線
            mysqli_stmt_close($stmt);
            mysqli_close($conn);

            // 更新成功，導回列表頁
            header("Location: index.php");
            exit;
        } else {
            // 更新失敗顯示訊息
            $msg = "<div class='alert alert-danger'>更新失敗</div>";
        }
    }
}

// 2. 顯示原始資料 (當只有 GET 請求時，或 POST 失敗後)
else {
    // 初始化 Statement 以查詢原資料
    $stmt = mysqli_stmt_init($conn);
    // 查詢目前 postid 的資料
    $sql = "SELECT company, content FROM job WHERE postid = ?";

    if (mysqli_stmt_prepare($stmt, $sql)) {
        // 綁定 postid 參數
        mysqli_stmt_bind_param($stmt, "i", $postid);
        // 執行查詢
        mysqli_stmt_execute($stmt);
        // 將結果綁定到 PHP 變數
        mysqli_stmt_bind_result($stmt, $company, $content);
        // 取出資料 (Fetch)
        mysqli_stmt_fetch($stmt);
        // 關閉 Statement (注意：這裡關閉後，下方變數仍保留值)
        mysqli_stmt_close($stmt);
    }
}

// 關閉資料庫連線
mysqli_close($conn);
?>

<div class="container my-5">
    <!-- 修改表單 -->
    <!-- action 指向本頁，並保留 postid 參數 -->
    <form action="job_update.php?postid=<?= $postid ?>&action=confirmed" method="post">

        <!-- 隱藏欄位 (Hidden Input) -->
        <!-- 再次傳送 postid 以便 POST 處理區塊能接收到 ID -->
        <input type="hidden" name="postid" value="<?= $postid ?>">

        <div class="mb-3 row">
            <label for="_company" class="col-sm-2 col-form-label">主辦單位</label>
            <div class="col-sm-10">
                <!-- 顯示原本的 $company 資料在 value 屬性中 -->
                <input type="text" class="form-control" name="company" id="_company" placeholder="公司名稱"
                    value="<?= htmlspecialchars($company) ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="_content" class="form-label">活動內容</label>
            <!-- 顯示原本的 $content 資料在 textarea 內容中 -->
            <textarea class="form-control" name="content" id="_content" rows="10"
                required><?= htmlspecialchars($content) ?></textarea>
        </div>

        <!-- 送出按鈕 -->
        <input class="btn btn-primary" type="submit" value="送出">
    </form>

    <!-- 顯示錯誤訊息 (如果有) -->
    <?= $msg ?>
</div>

<?php
// 引入頁尾
require_once "footer.php";
?>