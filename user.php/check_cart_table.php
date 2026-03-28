<?php
require 'connect.php';

try {
    // Kiểm tra xem bảng cart đã tồn tại chưa
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'cart'");
    $stmt->execute();
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "Bảng cart đã tồn tại.<br>";
        
        // Hiển thị cấu trúc bảng
        $stmt = $pdo->prepare("DESCRIBE cart");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Cấu trúc bảng cart:</h3>";
        echo "<pre>";
        print_r($columns);
        echo "</pre>";
        
        // Kiểm tra dữ liệu trong bảng
        $stmt = $pdo->prepare("SELECT * FROM cart LIMIT 10");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Dữ liệu trong bảng cart:</h3>";
        if (count($data) > 0) {
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        } else {
            echo "Không có dữ liệu trong bảng cart.";
        }
    } else {
        echo "Bảng cart chưa tồn tại. Đang tạo bảng...<br>";
        
        // Tạo bảng cart
        $pdo->exec("
            CREATE TABLE cart (
                cart_id INT(11) NOT NULL AUTO_INCREMENT,
                user_id INT(11) NOT NULL,
                product_id VARCHAR(50) NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                image VARCHAR(255),
                quantity INT(11) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (cart_id),
                KEY (user_id),
                KEY (product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        echo "Đã tạo bảng cart thành công!";
    }
    
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>