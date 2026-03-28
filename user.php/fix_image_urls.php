<?php
// Kết nối database
require 'connect.php';

// Xử lý form cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $image_url = $_POST['image_url'];
    
    $stmt = $pdo->prepare("UPDATE storybooks SET image = ? WHERE id = ?");
    $result = $stmt->execute([$image_url, $id]);
    
    if ($result) {
        $message = "Đã cập nhật URL ảnh cho sách ID: $id";
    } else {
        $error = "Lỗi khi cập nhật URL ảnh";
    }
}

// Lấy danh sách sách
$stmt = $pdo->query("SELECT id, product_name, image FROM storybooks ORDER BY id");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa URL ảnh</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .book-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            background-color: #fff;
        }
        .book-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .image-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 15px;
        }
        .image-preview {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .image-preview img {
            max-width: 300px;
            max-height: 300px;
            display: block;
            margin: 0 auto 10px;
        }
        .url-list {
            margin-bottom: 15px;
        }
        .url-item {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .url-text {
            word-break: break-all;
            font-family: monospace;
            margin-bottom: 5px;
        }
        .url-actions {
            display: flex;
            gap: 10px;
        }
        .url-actions button {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .edit-form {
            margin-top: 15px;
        }
        .edit-form textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: monospace;
        }
        .edit-form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .edit-form button:hover {
            background-color: #45a049;
        }
        .tips {
            background-color: #e7f3fe;
            border-left: 6px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
        }
        .tips h3 {
            margin-top: 0;
        }
        .tips ul {
            margin-bottom: 0;
        }
        .fix-suggestion {
            background-color: #fff3cd;
            border-left: 6px solid #ffc107;
            padding: 15px;
            margin-top: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sửa URL ảnh có độ phân giải thấp</h1>
        
        <?php if (isset($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="tips">
            <h3>Hướng dẫn sửa URL ảnh:</h3>
            <ul>
                <li>URL hiện tại có thể đang trỏ đến ảnh có độ phân giải thấp (s45x45)</li>
                <li>Thay thế phần "s45x45" trong URL bằng "s1000x1000" để có ảnh chất lượng cao hơn</li>
                <li>Hoặc thay thế bằng "original" để lấy ảnh gốc với độ phân giải cao nhất</li>
                <li>Nếu URL không hoạt động, hãy thử tìm ảnh khác từ các nguồn như Google Images</li>
            </ul>
        </div>
        
        <?php foreach ($books as $book): ?>
            <div class="book-item">
                <div class="book-title"><?= htmlspecialchars($book['product_name']) ?> (ID: <?= $book['id'] ?>)</div>
                
                <?php
                // Tách URL ảnh
                $images = [];
                if (!empty($book['image'])) {
                    $images = array_map('trim', explode(';', $book['image']));
                }
                
                // Hiển thị ảnh đầu tiên (nếu có)
                if (!empty($images[0])):
                    $first_image = $images[0];
                    
                    // Kiểm tra xem URL có chứa "s45x45" không
                    $has_low_res = strpos($first_image, 's45x45') !== false;
                    
                    // Tạo URL có độ phân giải cao hơn
                    $high_res_url = str_replace('s45x45', 's1000x1000', $first_image);
                    $original_url = str_replace('s45x45', 'original', $first_image);
                ?>
                
                <div class="image-container">
                    <div class="image-preview">
                        <h4>Ảnh hiện tại:</h4>
                        <img src="<?= htmlspecialchars($first_image) ?>" alt="Ảnh hiện tại">
                        <div>URL hiện tại</div>
                    </div>
                    
                    <?php if ($has_low_res): ?>
                    <div class="image-preview">
                        <h4>Ảnh độ phân giải cao hơn:</h4>
                        <img src="<?= htmlspecialchars($high_res_url) ?>" alt="Ảnh độ phân giải cao" onerror="this.src='https://via.placeholder.com/300x300?text=Không+tải+được'; this.style.border='2px solid red';">
                        <div>URL độ phân giải cao hơn</div>
                    </div>
                    
                    <div class="image-preview">
                        <h4>Ảnh gốc:</h4>
                        <img src="<?= htmlspecialchars($original_url) ?>" alt="Ảnh gốc" onerror="this.src='https://via.placeholder.com/300x300?text=Không+tải+được'; this.style.border='2px solid red';">
                        <div>URL ảnh gốc</div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($has_low_res): ?>
                <div class="fix-suggestion">
                    <p><strong>Phát hiện:</strong> URL ảnh có độ phân giải thấp (s45x45)</p>
                    <p>Bạn có thể thay thế URL hiện tại bằng một trong các URL sau để có ảnh chất lượng cao hơn:</p>
                    <ul>
                        <li><strong>URL độ phân giải cao hơn:</strong> <code><?= htmlspecialchars($high_res_url) ?></code></li>
                        <li><strong>URL ảnh gốc:</strong> <code><?= htmlspecialchars($original_url) ?></code></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php endif; ?>
                
                <form class="edit-form" method="post">
                    <input type="hidden" name="id" value="<?= $book['id'] ?>">
                    <textarea name="image_url" placeholder="Nhập URL ảnh (nhiều URL cách nhau bằng dấu chấm phẩy)"><?= htmlspecialchars($book['image'] ?? '') ?></textarea>
                    <button type="submit" name="update">Cập nhật URL ảnh</button>
                </form>
                
                <div>
                    <a href="readdetail.php?product_name=<?= urlencode($book['product_name']) ?>" target="_blank">Xem sách</a>
                </div>
            </div>
        <?php endforeach; ?>
        
        <p><a href="readhistory.php">Quay lại trang danh sách sách</a></p>
    </div>
</body>
</html>