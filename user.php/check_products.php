<?php
require 'connect.php';

// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Kiểm tra bảng products
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    $productsExists = $stmt->rowCount() > 0;
    
    echo "<h2>Kiểm tra bảng products</h2>";
    echo "<p>Bảng products: " . ($productsExists ? "Tồn tại" : "Không tồn tại") . "</p>";
    
    if ($productsExists) {
        // Hiển thị cấu trúc bảng
        $stmt = $pdo->query("DESCRIBE products");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Cấu trúc bảng products:</h3>";
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
        
        // Hiển thị dữ liệu mẫu
        $stmt = $pdo->query("SELECT * FROM products LIMIT 5");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($products) > 0) {
            echo "<h3>Dữ liệu mẫu (5 sản phẩm đầu tiên):</h3>";
            echo "<table border='1'>";
            
            // Hiển thị tiêu đề cột
            echo "<tr>";
            foreach (array_keys($products[0]) as $columnName) {
                echo "<th>" . htmlspecialchars($columnName) . "</th>";
            }
            echo "</tr>";
            
            // Hiển thị dữ liệu
            foreach ($products as $product) {
                echo "<tr>";
                foreach ($product as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>Không có dữ liệu trong bảng products.</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p>Lỗi: " . $e->getMessage() . "</p>";
}
?>