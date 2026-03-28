<?php
// Kết nối database
require 'connect.php';

// Lấy thông tin ảnh từ bảng storybooks
$stmt = $pdo->query("SELECT id, product_name, image FROM storybooks LIMIT 1");
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if ($book) {
    echo "<h2>Kiểm tra URL ảnh cho sách: " . htmlspecialchars($book['product_name']) . "</h2>";
    
    // Tách URL ảnh
    $images = [];
    if (!empty($book['image'])) {
        $images = array_map('trim', explode(';', $book['image']));
    }
    
    echo "<p>Số lượng URL ảnh: " . count($images) . "</p>";
    
    // Hiển thị từng URL ảnh
    foreach ($images as $index => $image_url) {
        echo "<div style='margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 10px;'>";
        echo "<h3>Ảnh #" . ($index + 1) . "</h3>";
        echo "<p>URL: <a href='" . htmlspecialchars($image_url) . "' target='_blank'>" . htmlspecialchars($image_url) . "</a></p>";
        
        // Kiểm tra URL
        $url_parts = parse_url($image_url);
        if (!$url_parts || empty($url_parts['scheme']) || empty($url_parts['host'])) {
            echo "<p style='color: red;'>URL không hợp lệ!</p>";
        } else {
            // Kiểm tra xem URL có phải là ảnh không
            $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            $path_parts = pathinfo($url_parts['path']);
            $is_image = false;
            
            if (isset($path_parts['extension'])) {
                $extension = strtolower($path_parts['extension']);
                $is_image = in_array($extension, $image_extensions);
            }
            
            if (!$is_image) {
                echo "<p style='color: orange;'>URL có thể không phải là ảnh (không có phần mở rộng ảnh phổ biến).</p>";
            }
        }
        
        // Hiển thị ảnh với kích thước khác nhau
        echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";
        
        echo "<div>";
        echo "<h4>Kích thước gốc:</h4>";
        echo "<img src='" . htmlspecialchars($image_url) . "' style='max-width: 300px; max-height: 300px; border: 1px solid #ccc;'>";
        echo "</div>";
        
        echo "<div>";
        echo "<h4>Kích thước 450x600 (object-fit: contain):</h4>";
        echo "<img src='" . htmlspecialchars($image_url) . "' style='width: 450px; height: 600px; object-fit: contain; border: 1px solid #ccc;'>";
        echo "</div>";
        
        echo "<div>";
        echo "<h4>Kích thước 450x600 (object-fit: cover):</h4>";
        echo "<img src='" . htmlspecialchars($image_url) . "' style='width: 450px; height: 600px; object-fit: cover; border: 1px solid #ccc;'>";
        echo "</div>";
        
        echo "</div>";
        echo "</div>";
    }
} else {
    echo "<p>Không tìm thấy sách nào trong cơ sở dữ liệu.</p>";
}
?>