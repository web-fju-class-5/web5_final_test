<?php
// logout.php
// --------------------------------------------------------
// 用途：處理使用者登出邏輯
// --------------------------------------------------------

// 引入 header.php
// 這裡主要目的是為了使用 session_start() (若 header 內有) 以及保持版面一致性 (雖然登出頁面通常看不到 HTML)
// 但更重要的是確保 PHP session 機制有被初始化
include "header.php";

// session_destroy()
// 這是登出的核心指令，它會銷毀當前 Server 端儲存的所有 Session 資料
// 這代表 $_SESSION['account'] 等變數將被清空，使用者狀態變為「未登入」
session_destroy();

// header("Location: ...")
// 設定 HTTP 回應標頭，通知瀏覽器跳轉頁面
// 登出完成後，將使用者引導回登入頁面 (login.php)
header("Location: login.php");

// 腳本結束，確保不會有額外輸出影響 header 跳轉
?>