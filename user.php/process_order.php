<?php
session_start();
require 'connect.php';

// Kiểm tra xem có dữ liệu POST không
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ POST request
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }
    
    try {
        // Bắt đầu transaction
        $pdo->beginTransaction();
        
        // Lấy thông tin đơn hàng
        $orderId = $data['orderId'];
        $name = $data['name'];
        $email = $data['email'];
        $phone = $data['phone'];
        $address = $data['address'];
        $totalPrice = $data['totalPrice'];
        $paymentMethod = isset($data['paymentMethod']) ? $data['paymentMethod'] : null;
        if (!$paymentMethod) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu phương thức thanh toán']);
            exit;
        }
        $status = isset($data['status']) ? $data['status'] : 'pending';
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        // Thêm đơn hàng vào bảng orders
        $orderStmt = $pdo->prepare("
            INSERT INTO orders (order_id, user_id, name, email, phone, address, total_price, payment_method, status)
            VALUES (:order_id, :user_id, :name, :email, :phone, :address, :total_price, :payment_method, :status)
        ");
        
        $orderStmt->bindParam(':order_id', $orderId);
        $orderStmt->bindParam(':user_id', $userId);
        $orderStmt->bindParam(':name', $name);
        $orderStmt->bindParam(':email', $email);
        $orderStmt->bindParam(':phone', $phone);
        $orderStmt->bindParam(':address', $address);
        $orderStmt->bindParam(':total_price', $totalPrice);
        $orderStmt->bindParam(':payment_method', $paymentMethod);
        $orderStmt->bindParam(':status', $status);
        
        $orderStmt->execute();
        
        // Lấy ID của đơn hàng vừa thêm
        $orderDbId = $pdo->lastInsertId();
        
        // Thêm chi tiết đơn hàng vào bảng order_details
        $detailStmt = $pdo->prepare("
            INSERT INTO order_details (order_id, product_id, quantity, price)
            VALUES (:order_id, :product_id, :quantity, :price)
        ");
        
        // Kiểm tra xem có dữ liệu items trong request không
        if (isset($data['items']) && is_array($data['items'])) {
            // Sử dụng dữ liệu từ request
            $items = $data['items'];
        } else if ($userId !== null) {
            // Lấy dữ liệu từ database nếu có user_id
            $cartStmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id");
            $cartStmt->execute([':user_id' => $userId]);
            $items = [];
            while ($row = $cartStmt->fetch(PDO::FETCH_ASSOC)) {
                $items[] = [
                    'id' => $row['product_id'],
                    'quantity' => $row['quantity'],
                    'price' => $row['price']
                ];
            }
        } else {
            $items = [];
        }
        
        foreach ($items as $item) {
            try {
                // Thêm chi tiết đơn hàng
                $productId = (int)$item['id'];
                $quantity = (int)$item['quantity'];
                $price = (float)$item['price'];
                
                $detailStmt->bindParam(':order_id', $orderDbId);
                $detailStmt->bindParam(':product_id', $productId);
                $detailStmt->bindParam(':quantity', $quantity);
                $detailStmt->bindParam(':price', $price);
                $detailStmt->execute();
            } catch (PDOException $itemError) {
                // Bỏ qua lỗi và tiếp tục với sản phẩm tiếp theo
                continue;
            }
        }
        
        // Xóa giỏ hàng sau khi đặt hàng thành công
        if ($userId !== null) {
            $deleteCartStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
            $deleteCartStmt->execute([':user_id' => $userId]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Trả về kết quả thành công
        echo json_encode([
            'success' => true,
            'message' => 'Đơn hàng đã được lưu thành công',
            'orderId' => $orderId
        ]);
        
    } catch (PDOException $e) {
        // Rollback transaction nếu có lỗi
        $pdo->rollBack();
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lưu đơn hàng: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
}
?>