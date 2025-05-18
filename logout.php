<?php
// logout.php
// 1. Bắt đầu session nếu chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Xóa toàn bộ session
$_SESSION = [];
session_destroy();

// 3. Chuyển về trang chủ hoặc login
header('Location: index.php');
exit;
