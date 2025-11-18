<?php
// users.php - 儲存使用者資料的關聯式陣列
// key = 帳號, value = 關聯陣列包含 password, name, role
$users = [
  "root"  => ["password" => "password", "name" => "管理員", "role" => "teacher"],
  "user1" => ["password" => "pw1",       "name" => "小明",   "role" => "student"],
  "user2" => ["password" => "pw2",       "name" => "小華",   "role" => "student"],
  "user3" => ["password" => "pw3",       "name" => "小美",   "role" => "student"],
  "user4" => ["password" => "pw4",       "name" => "小強",   "role" => "student"],
];
