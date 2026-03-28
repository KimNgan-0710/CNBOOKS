<?php
session_start();

// Trả về kết quả dưới dạng JSON
header('Content-Type: application/json');

// Kiểm tra xem người dùng đã đăng nhập chưa
if (isset($_SESSION['user_id'])) {
    echo json_encode(['logged_in' => true, 'user_id' => $_SESSION['user_id']]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>