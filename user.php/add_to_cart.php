<?php
session_start();
require 'connect.php';

// Đảm bảo phản hồi là JSON
header('Content-Type: application/json');

// Xác định loại request
$isJsonRequest = false;
$requestData = [];

// Kiểm tra xem có dữ liệu JSON không
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
if (strpos($contentType, 'application/json') !== false) {
    $rawData = file_get_contents('php://input');
    $requestData = json_decode($rawData, true);
    $isJsonRequest = true;
} else {
    // Nếu không phải JSON, lấy dữ liệu từ POST
    $requestData = $_POST;
}

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng!'
    ]);
    exit;
}

// Lấy dữ liệu từ request
if ($isJsonRequest) {
    // Lấy dữ liệu từ JSON
    $product_id = isset($requestData['product_id']) ? $requestData['product_id'] : null;
    $product_name = isset($requestData['product_name']) ? $requestData['product_name'] : null;
    $price = isset($requestData['price']) ? (float)$requestData['price'] : 0;
    $image = isset($requestData['image']) ? $requestData['image'] : null;
    $quantity = isset($requestData['quantity']) ? (int)$requestData['quantity'] : 1;
} else {
    // Lấy dữ liệu từ POST
    $product_id = isset($requestData['product_id']) ? $requestData['product_id'] : null;
    $product_name = isset($requestData['product_name']) ? $requestData['product_name'] : null;
    $price = isset($requestData['price']) ? (float)$requestData['price'] : 0;
    $image = isset($requestData['image']) ? $requestData['image'] : null;
    $quantity = isset($requestData['quantity']) ? (int)$requestData['quantity'] : 1;
}

$user_id = $_SESSION['user_id'];

// Kiểm tra dữ liệu đầu vào
if (!$product_id || !$product_name || $price <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu sản phẩm không hợp lệ!'
    ]);
    exit;
}

try {
    // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
    $checkStmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id");
    $checkStmt->execute([
        ':user_id' => $user_id,
        ':product_id' => $product_id
    ]);
    
    $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingItem) {
        // Nếu sản phẩm đã có trong giỏ hàng, cập nhật số lượng
        $newQuantity = $existingItem['quantity'] + $quantity;
        
        $updateStmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE cart_id = :cart_id");
        $updateStmt->execute([
            ':quantity' => $newQuantity,
            ':cart_id' => $existingItem['cart_id']
        ]);
    } else {
        // Nếu sản phẩm chưa có trong giỏ hàng, thêm mới
        $insertStmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, product_name, price, image, quantity) 
                                     VALUES (:user_id, :product_id, :product_name, :price, :image, :quantity)");
        $insertStmt->execute([
            ':user_id' => $user_id,
            ':product_id' => $product_id,
            ':product_name' => $product_name,
            ':price' => $price,
            ':image' => $image,
            ':quantity' => $quantity
        ]);
    }
    
    // Lấy tổng số sản phẩm trong giỏ hàng
    $countStmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = :user_id");
    $countStmt->execute([':user_id' => $user_id]);
    $result = $countStmt->fetch(PDO::FETCH_ASSOC);
    $cartCount = $result['total'] ? (int)$result['total'] : 0;
    
    // Trả về kết quả thành công
    echo json_encode([
        'success' => true,
        'message' => 'Sản phẩm đã được thêm vào giỏ hàng!',
        'cart_count' => $cartCount
    ]);
    
} catch (PDOException $e) {
    // Xử lý lỗi
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi thêm sản phẩm vào giỏ hàng: ' . $e->getMessage()
    ]);
}
?>