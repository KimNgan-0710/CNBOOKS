<?php
require 'connect.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Không có ID đơn hàng']);
    exit;
}

$orderId = $_GET['id'];

// Lấy thông tin đơn hàng
$stmt = $pdo->prepare("SELECT o.*, u.username FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      WHERE o.id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode(['error' => 'Không tìm thấy đơn hàng']);
    exit;
}

// Lấy chi tiết sản phẩm
$stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi 
                      JOIN products p ON oi.product_id = p.id 
                      WHERE oi.order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format dữ liệu trả về
$response = [
    'shipping_name' => $order['shipping_name'] ?? '',
    'shipping_phone' => $order['shipping_phone'] ?? '',
    'shipping_address' => $order['shipping_address'] ?? '',
    'total_price' => $order['total_price'],
    'items' => $items
];

echo json_encode($response);
?> 