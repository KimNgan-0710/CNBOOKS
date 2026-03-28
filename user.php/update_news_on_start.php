<?php
// Kết nối database
require_once 'connect.php';

// Hiển thị lỗi PHP (chỉ khi cần debug)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Hàm cập nhật bảng news từ readbooks
function updateNewsFromReadbooks() {
    global $pdo;
    
    try {
        // Kiểm tra xem bảng readbooks có dữ liệu không
        $check_readbooks = $pdo->query("SELECT COUNT(*) FROM readbooks");
        $count_readbooks = $check_readbooks->fetchColumn();
        
        if ($count_readbooks == 0) {
            // Không có dữ liệu trong bảng readbooks
            return false;
        }
        
        // Xóa dữ liệu cũ trong bảng news
        $pdo->exec("DELETE FROM news");
        
        // Lấy 5 cuốn sách có lượt đọc cao nhất từ bảng readbooks
        $stmt = $pdo->query("SELECT product_name, type_name, image, read_count FROM readbooks ORDER BY read_count DESC LIMIT 5");
        $top_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($top_books)) {
            // Không có sách nào được tìm thấy
            return false;
        }
        
        // Chuẩn bị câu lệnh INSERT
        $insert_stmt = $pdo->prepare("INSERT INTO news (product_name, type_name, image_url, read_count) VALUES (?, ?, ?, ?)");
        
        // Thêm từng cuốn sách vào bảng news
        foreach ($top_books as $book) {
            $insert_stmt->execute([
                $book['product_name'],
                $book['type_name'],
                $book['image'],
                $book['read_count']
            ]);
        }
        
        return true;
    } catch (PDOException $e) {
        // Ghi log lỗi
        error_log("Lỗi cập nhật bảng news: " . $e->getMessage());
        return false;
    }
}

// Luôn cập nhật bảng news khi trang được tải
updateNewsFromReadbooks();
?>