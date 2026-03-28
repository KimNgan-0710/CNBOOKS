<?php
session_start();
require 'connect.php';

// Đảm bảo phản hồi là JSON
header('Content-Type: application/json');

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn chưa đăng nhập!',
        'cart' => []
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Lấy dữ liệu giỏ hàng từ database
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Chuyển đổi dữ liệu để phù hợp với định dạng giỏ hàng trong localStorage
    $cart = [];
    foreach ($cartItems as $item) {
        $cart[] = [
            'id' => $item['product_id'],
            'name' => $item['product_name'],
            'price' => (float)$item['price'],
            'image' => $item['image'],
            'quantity' => (int)$item['quantity']
        ];
    }
    
    // Trả về kết quả thành công
    echo json_encode([
        'success' => true,
        'message' => 'Lấy dữ liệu giỏ hàng thành công!',
        'cart' => $cart
    ]);
    
} catch (PDOException $e) {
    // Trả về thông báo lỗi
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy dữ liệu giỏ hàng: ' . $e->getMessage(),
        'cart' => []
    ]);
}
?>