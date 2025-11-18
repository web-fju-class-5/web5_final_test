<?php
// login.php
include "header.php";
include "db.php";

$redirect = $_GET['redirect'] ?? 'index.php';

if ($_POST) {
    $account = $_POST["account"] ?? "";
    $password = $_POST["password"] ?? "";

    $account_safe = mysqli_real_escape_string($conn, $account);
    $password_safe = mysqli_real_escape_string($conn, $password);

    $sql = "SELECT * FROM user WHERE account='$account_safe' AND password='$password_safe'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $_SESSION["account"] = $row["account"];
        $_SESSION["name"] = $row["name"];
        $_SESSION["role"] = $row["role"];
      
        header("Location: $redirect");
        exit;
    } else {
        header("Location: login.php?msg=帳號或密碼錯誤&redirect=" . urlencode($redirect));
        exit;
    }
}

$msg = $_GET["msg"] ?? "";
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h4 class="card-title mb-4">登入</h4>
          <form method="post" action="login.php?redirect=<?=htmlspecialchars($redirect)?>">
            <div class="mb-3">
              <label for="account" class="form-label">帳號</label>
              <input type="text" class="form-control" id="account" name="account" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">密碼</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">登入</button>
          </form>
          <?php if ($msg): ?>
            <div class="alert alert-danger mt-3"><?=htmlspecialchars($msg)?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>
