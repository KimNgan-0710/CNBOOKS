<?php
session_start();
require 'connect.php';

// Đảm bảo phản hồi là JSON
header('Content-Type: application/json');

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập để đồng bộ giỏ hàng!'
    ]);
    exit;
}

// Kiểm tra xem có dữ liệu POST không
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không hợp lệ!'
    ]);
    exit;
}

// Lấy dữ liệu từ request
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!$data || !isset($data['cart']) || !is_array($data['cart'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu giỏ hàng không hợp lệ!'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $data['cart'];

try {
    // Bắt đầu transaction
    $pdo->beginTransaction();
    
    // Xóa tất cả các mục trong giỏ hàng hiện tại của người dùng
    $deleteStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
    $deleteStmt->execute([':user_id' => $user_id]);
    
    // Thêm các mục mới từ localStorage
    if (!empty($cart)) {
        $insertStmt = $pdo->prepare("
            INSERT INTO cart (user_id, product_id, product_name, price, image, quantity) 
            VALUES (:user_id, :product_id, :product_name, :price, :image, :quantity)
        ");
        
        foreach ($cart as $item) {
            $insertStmt->execute([
                ':user_id' => $user_id,
                ':product_id' => $item['id'],
                ':product_name' => $item['name'],
                ':price' => $item['price'],
                ':image' => $item['image'],
                ':quantity' => $item['quantity']
            ]);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Lấy tổng số sản phẩm trong giỏ hàng
    $countStmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = :user_id");
    $countStmt->execute([':user_id' => $user_id]);
    $result = $countStmt->fetch(PDO::FETCH_ASSOC);
    $cartCount = $result['total'] ? (int)$result['total'] : 0;
    
    // Trả về kết quả thành công
    echo json_encode([
        'success' => true,
        'message' => 'Giỏ hàng đã được đồng bộ thành công!',
        'cart_count' => $cartCount
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction nếu có lỗi
    $pdo->rollBack();
    
    // Trả về thông báo lỗi
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi đồng bộ giỏ hàng: ' . $e->getMessage()
    ]);
}
?>