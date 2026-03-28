<?php
// Kết nối database
require 'connect.php';

try {
    // Bắt đầu transaction
    $pdo->beginTransaction();
    
    // Xóa dữ liệu cũ trong bảng news
    $pdo->exec("TRUNCATE TABLE news");
    
    // Lấy 5 cuốn sách có lượt đọc cao nhất từ bảng readbooks
    $stmt = $pdo->query("SELECT product_name, type_name, image, read_count FROM readbooks ORDER BY read_count DESC LIMIT 5");
    $top_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Chuẩn bị câu lệnh INSERT
    $insert_stmt = $pdo->prepare("INSERT INTO news (product_name, type_name, image_url, read_count) VALUES (:product_name, :type_name, :image_url, :read_count)");
    
    // Thêm từng cuốn sách vào bảng news
    foreach ($top_books as $book) {
        $insert_stmt->execute([
            'product_name' => $book['product_name'],
            'type_name' => $book['type_name'],
            'image_url' => $book['image'],
            'read_count' => $book['read_count']
        ]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Ghi log
    $log_message = date('Y-m-d H:i:s') . " - Đã cập nhật thành công 5 cuốn sách có lượt đọc cao nhất vào bảng news.\n";
    file_put_contents('news_update_log.txt', $log_message, FILE_APPEND);
    
    echo "Đã cập nhật thành công 5 cuốn sách có lượt đọc cao nhất vào bảng news.";
    
} catch (PDOException $e) {
    // Rollback transaction nếu có lỗi
    $pdo->rollBack();
    
    // Ghi log lỗi
    $error_message = date('Y-m-d H:i:s') . " - Lỗi: " . $e->getMessage() . "\n";
    file_put_contents('news_update_error_log.txt', $error_message, FILE_APPEND);
    
    echo "Lỗi: " . $e->getMessage();
}
?>