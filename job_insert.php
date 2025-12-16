<?php
// job_insert.php
// --------------------------------------------------------
// 用途：新增職缺/活動用的表單處理頁面
// --------------------------------------------------------

// 引入頁首 (session_start() 已在 header.php 中啟動)
include "header.php";
// 引入資料庫連線
include "db.php";

// 1️⃣ 檢查是否有 POST 表單送出
// 當使用者填寫完表單並按下 Submit 時，$_POST 會包含送出的資料
if ($_POST) {
  // 接收來自表單的 'company' 與 'content' 欄位
  // 使用 ?? "" 是為了在欄位未定義時提供一個空字串作為預設值
  $company = $_POST["company"] ?? "";
  $content = $_POST["content"] ?? "";

  // 避免 SQL Injection (資料隱碼攻擊)
  // 使用 mysqli_real_escape_string 將特殊字元跳脫 (如單引號 ')
  // 雖然下面使用了 Prepared Statement，但這裡先處理並無不可，或者針對某些直接組字串的情況是必要的
  $company_safe = mysqli_real_escape_string($conn, $company);
  $content_safe = mysqli_real_escape_string($conn, $content);

  // 準備 SQL 插入指令
  // VALUES (?, ?, NOW()) 是 Prepared Statement 的佔位符
  // NOW() 是 MySQL 的函式，會填入當下時間
  $sql = "INSERT INTO job (company, content, pdate) VALUES (?, ?, NOW())";

  // 初始化一個 Statement 物件
  $stmt = mysqli_stmt_init($conn);

  // 準備 SQL
  // mysqli_stmt_prepare 負責檢查 SQL 語法正確性，並讓資料庫預先編譯
  if (mysqli_stmt_prepare($stmt, $sql)) {
    // 綁定參數
    // "ss" 代表兩個參數都是 String (字串)
    // 第一個 ? 對應 $company_safe
    // 第二個 ? 對應 $content_safe
    mysqli_stmt_bind_param($stmt, "ss", $company_safe, $content_safe);

    // 執行 SQL
    if (mysqli_stmt_execute($stmt)) {
      // ✅ 新增成功直接跳轉回 index.php (活動列表頁)
      header("Location: index.php");
      // exit 確保後續程式碼不執行，直接結束回應
      exit;
    } else {
      // 若 execute 回傳 false，代表執行失敗，顯示錯誤訊息
      $msg = "<div class='alert alert-danger'>新增失敗</div>";
    }
  }
  // 關閉 Statement
  mysqli_stmt_close($stmt);
}

// 關閉資料庫連線，釋放資源
mysqli_close($conn);
?>

<!-- HTML 表單區域 -->
<div class="container my-5">
  <!-- 表單 action 指向自己 (job_insert.php)，method 使用 post 傳送資料 -->
  <form action="job_insert.php" method="post">

    <!-- 主辦廠商輸入框 -->
    <div class="mb-3 row">
      <label for="_company" class="col-sm-2 col-form-label">求才廠商</label>
      <div class="col-sm-10">
        <!-- name="company" 對應到上面的 $_POST["company"] -->
        <!-- required 屬性強制使用者必須填寫 -->
        <input type="text" class="form-control" name="company" id="_company" placeholder="公司名稱" required>
      </div>
    </div>

    <!-- 活動內容輸入區域 -->
    <div class="mb-3">
      <label for="_content" class="form-label">求才內容</label>
      <!-- textarea 提供多行輸入 -->
      <textarea class="form-control" name="content" id="_content" rows="10" required></textarea>
    </div>

    <!-- 送出按鈕 -->
    <input class="btn btn-primary" type="submit" value="送出">
  </form>

</div>

<?php
// 引入頁尾
require_once "footer.php";
?>