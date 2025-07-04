<?php
// 1. Luôn phải bắt đầu session trước khi làm việc với nó
session_start();

// 2. Xóa tất cả các biến đã được lưu trong session
$_SESSION = array();

// 3. Hủy toàn bộ phiên session
// Thao tác này sẽ xóa session ID và dữ liệu liên quan phía server.
session_destroy();

// 4. Chuyển hướng người dùng về trang chủ
// Dùng ../ để đi từ thư mục 'php' ra thư mục gốc
header("Location: ../trangchu.html");

// 5. Dừng thực thi script để đảm bảo không có code nào chạy sau khi chuyển hướng
exit();
?>