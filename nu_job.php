<?php
// nu_job.php
// --------------------------------------------------------
// 用途：職缺/活動列表 (Job List) - 備用或測試頁面
// 功能：
// 1. 列表顯示所有職缺
// 2. 提供搜尋、排序、日期區間篩選
// 3. 管理員可進行新增、修改、刪除
// --------------------------------------------------------

$title = "求才資訊列表";

// 引入頁首
include "header.php";

try {
  // 引入資料庫連線
  require_once 'db.php';

  // 接收篩選條件 (POST)
  $order = $_POST["order"] ?? ""; // 排序欄位
  // 使用 escape string 處理搜尋文字
  $searchtxt = mysqli_real_escape_string($conn, $_POST["searchtxt"] ?? "");
  $date_start = $_POST["date_start"] ?? "";
  $date_end = $_POST["date_end"] ?? "";

  // 日期區間智慧調整：若起始日大於結束日，則自動交換
  if ($date_start && $date_end && $date_start > $date_end) {
    [$date_start, $date_end] = [$date_end, $date_start];
  }

  // 組合 WHERE 條件陣列
  $where = [];

  // 關鍵字搜尋：同時搜尋公司名稱 (company) 與內容 (content)
  if ($searchtxt) {
    $where[] = "(company like '%$searchtxt%' or content like '%$searchtxt%')";
  }

  // 日期篩選 (大於等於起始日)
  if ($date_start) {
    $where[] = "pdate >= '$date_start'";
  }

  // 日期篩選 (小於等於結束日)
  if ($date_end) {
    $where[] = "pdate <= '$date_end'";
  }

  // 初始 SQL 字串
  $sql = "select * from job";

  // 如果有 WHERE 條件，使用 implode 用 ' and ' 串接起來
  if (count($where) > 0) {
    $sql .= " where " . implode(' and ', $where);
  }

  // 如果有指定排序，加上 order by 子句
  // 這裡須注意 order 變數最好做白名單檢查 (雖然目前直接塞入)
  if ($order) {
    $sql .= " order by $order";
  }

  // 執行查詢
  $result = mysqli_query($conn, $sql);

  ?>
  <div class="container position-relative">

    <!-- 新增按鈕：只有管理員 (Role M) 可見 -->
    <?php if (!empty($_SESSION['role']) && strtoupper(trim($_SESSION['role'])) === 'M'): ?>
      <a href="job_insert.php" class="btn btn-primary position-absolute" style="top: 1rem; right: 1rem;">+</a>
    <?php endif; ?>

    <!-- 搜尋表單區域 -->
    <form action="job.php" method="post">
      <div class="row g-2 align-items-center mb-2">
        <div class="col-auto">
          <!-- 排序選單 -->
          <select name="order" aria-label="選擇排序欄位" class="form-select">
            <option selected value="">選擇排序欄位</option>
            <!-- 根據當前 $order 值設定 selected 狀態 -->
            <option value="company" <?= ($order == "company") ? 'selected' : '' ?>>營隊</option>
            <option value="content" <?= ($order == "content") ? 'selected' : '' ?>>內容</option>
            <option value="pdate" <?= ($order == "pdate") ? 'selected' : '' ?>>日期</option>
          </select>
        </div>

        <div class="col-auto">
          <!-- 關鍵字輸入框 -->
          <input placeholder="搜尋廠商及內容" value="<?= htmlspecialchars($searchtxt) ?>" type="text" name="searchtxt"
            class="form-control">
        </div>

        <div class="col-auto">
          <!-- 日期選擇 (起) -->
          <input type="date" name="date_start" class="form-control" value="<?= htmlspecialchars($date_start) ?>"
            placeholder="起始日期">
        </div>

        <div class="col-auto">
          <span>~</span>
        </div>

        <div class="col-auto">
          <!-- 日期選擇 (迄) -->
          <input type="date" name="date_end" class="form-control" value="<?= htmlspecialchars($date_end) ?>"
            placeholder="結束日期">
        </div>

        <div class="col-auto">
          <!-- 送出搜尋 -->
          <input class="btn btn-primary" type="submit" value="搜尋">
        </div>
      </div>
    </form>

    <!-- 資料表格 -->
    <table class="table table-bordered table-striped" id="job_table">
      <thead>
        <tr>
          <th>營隊</th>
          <th>內容</th>
          <th>日期</th>
          <th>編輯</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // 迴圈遍歷查詢結果
        while ($row = mysqli_fetch_assoc($result)) { ?>
          <tr>
            <td><?= $row["company"] ?></td>
            <td><?= $row["content"] ?></td>
            <td><?= $row["pdate"] ?></td>
            <td>
              <!-- 功能按鈕：修改與刪除 -->
              <!-- 連結帶上 postid 參數 -->
              <a href="job_update.php?postid=<?= $row["postid"] ?>" class="btn btn-primary btn-sm">修改</a>
              <a href="job_delete.php?postid=<?= $row["postid"] ?>" class="btn btn-danger btn-sm">刪除</a>
            </td>
          </tr>
          <?php
        }
        ?>
      </tbody>
    </table>
  </div>
  <?php
  // 關閉資料庫連線
  mysqli_close($conn);
}
// 錯誤處理 (例外捕獲)
catch (Exception $e) {
  echo 'Message: ' . $e->getMessage();
}

// 引入頁尾
require_once "footer.php";
?>