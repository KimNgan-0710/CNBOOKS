<?php
// Kết nối database
require 'connect.php';

// Hiển thị lỗi PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Kiểm tra và cập nhật bảng news</h2>";

// Kiểm tra dữ liệu trong bảng readbooks
try {
    $stmt_count = $pdo->query("SELECT COUNT(*) FROM readbooks");
    $count_readbooks = $stmt_count->fetchColumn();
    
    echo "<p>Số lượng bản ghi trong bảng readbooks: " . $count_readbooks . "</p>";
    
    if ($count_readbooks > 0) {
        // Hiển thị 5 bản ghi có lượt đọc cao nhất
        $stmt_top = $pdo->query("SELECT product_name, type_name, read_count FROM readbooks ORDER BY read_count DESC LIMIT 5");
        $top_books = $stmt_top->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>5 cuốn sách có lượt đọc cao nhất:</h3>";
        echo "<ul>";
        foreach ($top_books as $book) {
            echo "<li>" . htmlspecialchars($book['product_name']) . " - Loại: " . htmlspecialchars($book['type_name']) . " - Lượt đọc: " . $book['read_count'] . "</li>";
        }
        echo "</ul>";
        
        // Cập nhật bảng news
        try {
            // Xóa dữ liệu cũ trong bảng news
            $pdo->exec("TRUNCATE TABLE news");
            echo "<p>Đã xóa dữ liệu cũ trong bảng news.</p>";
            
            // Chuẩn bị câu lệnh INSERT
            $insert_stmt = $pdo->prepare("INSERT INTO news (product_name, type_name, image_url, read_count) VALUES (:product_name, :type_name, :image_url, :read_count)");
            
            // Lấy 5 cuốn sách có lượt đọc cao nhất
            $stmt = $pdo->query("SELECT product_name, type_name, image, read_count FROM readbooks ORDER BY read_count DESC LIMIT 5");
            $top_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Thêm từng cuốn sách vào bảng news
            $success_count = 0;
            foreach ($top_books as $book) {
                $result = $insert_stmt->execute([
                    'product_name' => $book['product_name'],
                    'type_name' => $book['type_name'],
                    'image_url' => $book['image'],
                    'read_count' => $book['read_count']
                ]);
                
                if ($result) {
                    $success_count++;
                    echo "<p>Đã thêm sách: " . htmlspecialchars($book['product_name']) . " vào bảng news.</p>";
                } else {
                    echo "<p>Lỗi khi thêm sách: " . htmlspecialchars($book['product_name']) . " vào bảng news.</p>";
                    echo "<p>Lỗi: " . print_r($insert_stmt->errorInfo(), true) . "</p>";
                }
            }
            
            echo "<p>Đã thêm thành công " . $success_count . " cuốn sách vào bảng news.</p>";
            
            // Kiểm tra lại bảng news
            $stmt_check = $pdo->query("SELECT COUNT(*) FROM news");
            $count_news = $stmt_check->fetchColumn();
            
            echo "<p>Số lượng bản ghi trong bảng news sau khi cập nhật: " . $count_news . "</p>";
            
            if ($count_news > 0) {
                // Hiển thị dữ liệu trong bảng news
                $stmt_news = $pdo->query("SELECT product_name, type_name, read_count FROM news ORDER BY read_count DESC");
                $news_list = $stmt_news->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h3>Dữ liệu trong bảng news:</h3>";
                echo "<ul>";
                foreach ($news_list as $news) {
                    echo "<li>" . htmlspecialchars($news['product_name']) . " - Loại: " . htmlspecialchars($news['type_name']) . " - Lượt đọc: " . $news['read_count'] . "</li>";
                }
                echo "</ul>";
            }
            
        } catch (PDOException $e) {
            echo "<p>Lỗi khi cập nhật bảng news: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Không có dữ liệu trong bảng readbooks.</p>";
    }
} catch (PDOException $e) {
    echo "<p>Lỗi khi truy vấn bảng readbooks: " . $e->getMessage() . "</p>";
}
?>