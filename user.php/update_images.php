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
    <title>Cập nhật URL ảnh</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .book-image {
            max-width: 150px;
            max-height: 200px;
            border-radius: 5px;
        }
        .image-placeholder {
            width: 150px;
            height: 200px;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #777;
            border-radius: 5px;
        }
        .image-count {
            background-color: #ff69b4;
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            display: inline-block;
            margin-top: 5px;
        }
        form {
            margin-top: 10px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .image-tips {
            background-color: #e7f3fe;
            border-left: 6px solid #2196F3;
            padding: 10px;
            margin-bottom: 20px;
        }
        .image-tips h3 {
            margin-top: 0;
        }
        .image-tips ul {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cập nhật URL ảnh cho sách</h1>
        
        <?php if (isset($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="image-tips">
            <h3>Hướng dẫn thêm URL ảnh:</h3>
            <ul>
                <li>URL ảnh phải là đường dẫn trực tiếp đến file ảnh (kết thúc bằng .jpg, .png, .gif, ...)</li>
                <li>Bạn có thể sử dụng các trang lưu trữ ảnh như Imgur, ImgBB, Flickr, ...</li>
                <li>Để lấy URL ảnh từ Google, nhấp chuột phải vào ảnh và chọn "Sao chép địa chỉ hình ảnh"</li>
                <li>Đảm bảo URL bắt đầu bằng http:// hoặc https://</li>
                <li><strong>Nhiều URL ảnh:</strong> Bạn có thể thêm nhiều URL ảnh bằng cách phân tách chúng bằng dấu chấm phẩy (;)</li>
                <li>Ví dụ: https://example.com/image1.jpg; https://example.com/image2.jpg</li>
            </ul>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên sách</th>
                    <th>Ảnh hiện tại</th>
                    <th>URL ảnh</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                <tr>
                    <td><?= $book['id'] ?></td>
                    <td><?= htmlspecialchars($book['product_name']) ?></td>
                    <td>
                        <?php if (!empty($book['image'])): ?>
                            <?php 
                            // Lấy URL ảnh đầu tiên nếu có nhiều ảnh
                            $images = explode(';', $book['image']);
                            $first_image = trim($images[0]);
                            ?>
                            <img 
                                src="<?= htmlspecialchars($first_image) ?>" 
                                alt="<?= htmlspecialchars($book['product_name']) ?>" 
                                class="book-image"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >
                            <div class="image-placeholder" style="display: none;">Ảnh không tải được</div>
                            <?php if (count($images) > 1): ?>
                                <div class="image-count"><?= count($images) ?> ảnh</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="image-placeholder">Chưa có ảnh</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="id" value="<?= $book['id'] ?>">
                            <input type="text" name="image_url" value="<?= htmlspecialchars($book['image'] ?? '') ?>" placeholder="Nhập URL ảnh">
                            <button type="submit" name="update">Cập nhật</button>
                        </form>
                    </td>
                    <td>
                        <a href="readdetail.php?product_name=<?= urlencode($book['product_name']) ?>" target="_blank">Xem sách</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p><a href="readhistory.php">Quay lại trang danh sách sách</a></p>
    </div>
</body>
</html>