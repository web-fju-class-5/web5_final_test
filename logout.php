<?php
//登出
include "header.php";
session_destroy();
header("Location: login.php");
?>