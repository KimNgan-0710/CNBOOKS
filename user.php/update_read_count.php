<?php
// Kết nối database
require 'connect.php';

// Kiểm tra xem có dữ liệu được gửi lên không
if (isset($_POST['product_name']) && isset($_POST['type_name'])) {
    $product_name = $_POST['product_name'];
    $type_name = $_POST['type_name'];
    
    try {
        // Cập nhật lượt đọc trong bảng readbooks
        $sql = "UPDATE readbooks SET read_count = read_count + 1 WHERE product_name = :product_name AND type_name = :type_name";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            'product_name' => $product_name,
            'type_name' => $type_name
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật lượt đọc thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật lượt đọc']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sách']);
}
?>