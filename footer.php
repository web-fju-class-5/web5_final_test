<?php
// footer.php
// --------------------------------------------------------
// 用途：網站頁尾元件
// 功能：
// 1. 顯示版權資訊、聯絡方式
// 2. 確保頁面底部版面一致性
// --------------------------------------------------------
?>

<!-- 頁尾區塊開始 -->
<!-- mt-auto: 在 Flexbox 佈局中自動推到最底部 (Sticky Footer 效果) -->
<!-- py-3: 上下 padding 間距 3 單位 -->
<!-- bg-dark text-white: 深色背景白字 -->
<footer class="footer mt-auto py-3 bg-dark text-white">
  <div class="container text-center">

    <!-- 聯絡方式標題 -->
    <h4 class="mb-3">聯絡方式</h4>

    <!-- 說明文字 -->
    <p class="mb-1">如果對系學會有任何問題，請隨時與我聯繫：</p>

    <!-- 聯絡資訊列表 (無樣式清單) -->
    <ul class="list-unstyled mb-0">
      <li>Email：benwu@im.fju.edu.tw</li>
      <li>電話：02-29053938</li>
    </ul>

  </div>
</footer>
<!-- 頁尾區塊結束 -->