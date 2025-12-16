<?php
// login.php
// --------------------------------------------------------
// 用途：使用者登入頁面
// 功能：提供表單輸入帳密，並驗證資料庫中的使用者資料
// --------------------------------------------------------

// 1. 引入共用的頁首檔案 (header.php)
// header.php 通常包含 HTML <head>宣告、CSS 引用、導覽列以及 session_start()
include "header.php";

// 2. 引入資料庫連線設定 (db.php)
// 這讓我們可以使用 $conn 變數來操作資料庫
include "db.php";

// 3. 接收並處理由其他頁面傳來的 'redirect' 參數 (GET)
// 如果使用者原本是想去別頁被攔截過來登入的，登入成功後我們要把他送回去
// ?? 'index.php' 代表如果沒有 redirect 參數，預設登入後去首頁
$redirect = $_GET['redirect'] ?? 'index.php';

// 4. 檢查是否有 POST 請求 (使用者按下了登入按鈕)
if ($_POST) {
  // 4-1. 接收表單欄位資料
  // 使用 ?? "" 防止未定義索引警告 (雖然 HTML required 已擋，但後端檢查更重要)
  $account = $_POST["account"] ?? "";
  $password = $_POST["password"] ?? "";

  // 4-2. 資料安全性處理 (防止 SQL Injection 注入攻擊)
  // mysqli_real_escape_string 會將特殊字符 (如 ' " \) 進行跳脫
  // 這是非常重要的資安防護步驟
  $account_safe = mysqli_real_escape_string($conn, $account);
  $password_safe = mysqli_real_escape_string($conn, $password);

  // 4-3. 組合 SQL 查詢語句
  // 查詢 user 資料表中，是否有帳號與密碼完全符合的紀錄
  $sql = "SELECT * FROM user WHERE account='$account_safe' AND password='$password_safe'";

  // 4-4. 執行查詢
  // $result 會是查詢結果的物件 (mysqli_result) 或 false (若語法錯誤)
  $result = $conn->query($sql);

  // 4-5. 判斷查詢結果
  // $result->num_rows > 0 表示有找到符合的帳號密碼，登入成功
  if ($result && $result->num_rows > 0) {
    // 從結果集中取出一筆資料轉為關聯陣列
    $row = $result->fetch_assoc();

    // 將使用者重要資訊寫入 Session
    // 這樣在其他頁面就能透過 if(isset($_SESSION['account'])) 來判斷是否登入
    $_SESSION["account"] = $row["account"]; // 帳號 (唯一識別)
    $_SESSION["name"] = $row["name"];       // 姓名 (顯示用)
    $_SESSION["role"] = $row["role"];       // 角色 (權限判斷用：M=管理員, S=學生...)

    // 登入成功，導向至目標頁面 (首頁或原本想去的頁面)
    header("Location: $redirect");
    exit; // 務必使用 exit 停止後續程式碼執行
  } else {
    // 4-6. 登入失敗
    // 導回 login.php 並帶上錯誤訊息 msg 與原本的 redirect 路徑
    // urlencode() 確保網址參數格式正確
    header("Location: login.php?msg=帳號或密碼錯誤&redirect=" . urlencode($redirect));
    exit;
  }
}

// 5. 接收 GET 傳來的錯誤訊息 (例如從上面 header 轉址回來的)
// 若有 msg 參數則存入變數，稍後在 HTML 中顯示
$msg = $_GET["msg"] ?? "";
?>

<!-- HTML 頁面開始 -->
<!-- 這裡使用 Bootstrap 的容器與卡片樣式來美化登入框 -->
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-4"> <!-- 寬度佔 4/12，置中顯示 -->
      <div class="card shadow"> <!-- shadow 增加陰影效果 -->
        <div class="card-body">
          <h4 class="card-title mb-4">登入</h4>

          <!-- 登入表單 -->
          <!-- action 指定送出到當前頁面 (並保留 redirect 參數) -->
          <!-- method="post" 確保密碼不會顯示在網址列 -->
          <form method="post" action="login.php?redirect=<?= htmlspecialchars($redirect) ?>">

            <div class="mb-3">
              <label for="account" class="form-label">帳號</label>
              <!-- required 屬性要求瀏覽器必填此欄位 -->
              <input type="text" class="form-control" id="account" name="account" required>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">密碼</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <!-- 送出按鈕，w-100 讓按鈕滿版寬度 -->
            <button type="submit" class="btn btn-primary w-100">登入</button>
          </form>

          <!-- 錯誤訊息顯示區 -->
          <!-- 若 $msg 變數不為空，則顯示紅色的 alert 區塊 -->
          <?php if ($msg): ?>
            <!-- htmlspecialchars 防止 XSS 攻擊，將特殊 HTML 符號轉義 -->
            <div class="alert alert-danger mt-3"><?= htmlspecialchars($msg) ?></div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</div>

<?php
// 引入頁尾 (包含 JS 載入或版權宣告)
include "footer.php";
?>