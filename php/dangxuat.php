<?php
session_start(); // mở session

// xóa toàn bộ session
session_unset();
session_destroy();

// chuyển về trang chủ
header("Location: index.php");
exit();
?>