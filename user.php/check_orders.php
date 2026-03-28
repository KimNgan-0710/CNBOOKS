<?php
require 'connect.php';

// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Kiểm tra bảng orders
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    $ordersExists = $stmt->rowCount() > 0;
    
    echo "<h2>Kiểm tra bảng orders</h2>";
    echo "<p>Bảng orders: " . ($ordersExists ? "Tồn tại" : "Không tồn tại") . "</p>";
    
    if ($ordersExists) {
        // Hiển thị cấu trúc bảng
        $stmt = $pdo->query("DESCRIBE orders");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Cấu trúc bảng orders:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Hiển thị dữ liệu đơn hàng
        $stmt = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 10");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($orders) > 0) {
            echo "<h3>Đơn hàng gần đây (10 đơn hàng):</h3>";
            echo "<table border='1'>";
            
            // Hiển thị tiêu đề cột
            echo "<tr>";
            foreach (array_keys($orders[0]) as $columnName) {
                echo "<th>" . htmlspecialchars($columnName) . "</th>";
            }
            echo "</tr>";
            
            // Hiển thị dữ liệu
            foreach ($orders as $order) {
                echo "<tr>";
                foreach ($order as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</table>";
            
            // Hiển thị chi tiết đơn hàng
            echo "<h3>Chi tiết đơn hàng:</h3>";
            
            foreach ($orders as $order) {
                echo "<h4>Chi tiết đơn hàng #" . htmlspecialchars($order['order_id']) . "</h4>";
                
                $stmt = $pdo->prepare("
                    SELECT od.*, p.product_name
                    FROM order_details od
                    JOIN products p ON od.product_id = p.product_id
                    WHERE od.order_id = :order_id
                ");
                $stmt->bindParam(':order_id', $order['id']);
                $stmt->execute();
                $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($orderDetails) > 0) {
                    echo "<table border='1'>";
                    
                    // Hiển thị tiêu đề cột
                    echo "<tr>";
                    foreach (array_keys($orderDetails[0]) as $columnName) {
                        echo "<th>" . htmlspecialchars($columnName) . "</th>";
                    }
                    echo "</tr>";
                    
                    // Hiển thị dữ liệu
                    foreach ($orderDetails as $detail) {
                        echo "<tr>";
                        foreach ($detail as $value) {
                            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                        }
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                } else {
                    echo "<p>Không có chi tiết đơn hàng.</p>";
                }
            }
        } else {
            echo "<p>Không có đơn hàng nào trong database.</p>";
        }
    }
    
    // Kiểm tra bảng order_details
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_details'");
    $orderDetailsExists = $stmt->rowCount() > 0;
    
    echo "<h2>Kiểm tra bảng order_details</h2>";
    echo "<p>Bảng order_details: " . ($orderDetailsExists ? "Tồn tại" : "Không tồn tại") . "</p>";
    
    if ($orderDetailsExists) {
        // Hiển thị cấu trúc bảng
        $stmt = $pdo->query("DESCRIBE order_details");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Cấu trúc bảng order_details:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<p>Lỗi: " . $e->getMessage() . "</p>";
}
?>