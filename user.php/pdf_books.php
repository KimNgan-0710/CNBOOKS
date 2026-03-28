<?php
// Không cần gọi session_start() ở đây vì đã được gọi trong header.php
include 'header.php';
require_once 'connect.php';

// Lấy danh sách tất cả sách PDF từ bảng products có type_name là "Tài liệu Edufly"
$stmt = $pdo->prepare("SELECT * FROM products WHERE type_name = ? ORDER BY product_id DESC");
$stmt->execute(["Tài liệu toán học"]);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

$books_no_pdf = $pdo->query("SELECT product_id, product_name FROM products WHERE type_name = 'Tài liệu toán học' AND (file_pdf IS NULL OR file_pdf = '') ORDER BY product_name")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài Liệu toán học - CNBooks</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            margin-top: 100px; /* Để không bị che bởi header */
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-title {
            text-align: center;
            color: #4a90e2;
            margin-bottom: 30px;
            font-size: 32px;
            font-weight: 600;
        }

        .books-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-gap: 25px;
            margin-top: 20px;
        }

        .book-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative; /* Để hình ảnh có thể vượt ra ngoài */
            padding-bottom: 10px; /* Thêm padding dưới */
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .book-image {
            width: 100%;
            height: 320px; /* Tăng chiều cao để hiển thị đủ hình ảnh */
            background-color: #e9f0f7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #4a90e2;
            overflow: hidden;
            border-radius: 10px 10px 0 0; /* Bo góc phần trên */
            position: relative; /* Để hình ảnh có thể vượt ra ngoài */
            margin: -18px -18px 15px -18px; /* Mở rộng ra ngoài card */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .book-image img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* Thay đổi từ cover sang contain để hiển thị đủ hình ảnh */
            transition: transform 0.5s ease;
            display: block;
            padding: 10px; /* Thêm padding để hình ảnh không sát viền */
        }
        
        .book-image img:hover {
            transform: scale(1.08);
        }
        
        .book-image i {
            font-size: 80px; /* Tăng kích thước icon */
            opacity: 0.8;
        }

        .book-info {
            padding: 0 15px 10px;
            flex-grow: 0; /* Thay đổi từ 1 sang 0 để không mở rộng */
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
        }

        .book-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            line-height: 1.3;
            position: relative;
            padding-bottom: 10px;
            height: 50px; /* Giới hạn chiều cao */
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Giới hạn số dòng */
            -webkit-box-orient: vertical;
            text-overflow: ellipsis;
        }
        
        .book-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: #4a90e2;
            border-radius: 3px;
        }

        .book-author {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .book-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3; /* Giới hạn số dòng */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .book-meta {
            font-size: 12px;
            color: #888;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            background-color: #f8f9fa;
            padding: 6px 10px;
            border-radius: 6px;
            border-left: 3px solid #4a90e2;
        }

        .book-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 5px; /* Giảm margin-top */
        }

        .view-btn {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 50px; /* Bo tròn nút */
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
            width: 100%; /* Nút chiếm toàn bộ chiều rộng */
        }

        .view-btn:hover {
            background: #3a7bc8;
            box-shadow: 0 6px 12px rgba(74, 144, 226, 0.4);
            transform: translateY(-2px);
        }
        
        .view-btn i {
            font-size: 16px;
        }

        .no-books {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 10px;
            grid-column: 1 / -1;
        }

        .upload-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .upload-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 15px;
        }

        .upload-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .upload-btn {
            background: #4caf50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .upload-btn:hover {
            background: #3d8b40;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .books-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .books-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title">📚 Tài Liệu Edufly</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']);
            ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']);
            ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 1): ?>
        <div class="upload-section">
            <h2 class="upload-title">Gắn file PDF cho sách đã có</h2>
            <form action="upload_pdf.php" method="post" enctype="multipart/form-data" class="upload-form">
                <select name="product_id" required>
                    <option value="">-- Chọn sách --</option>
                    <?php foreach ($books_no_pdf as $book): ?>
                        <option value="<?php echo $book['product_id']; ?>"><?php echo htmlspecialchars($book['product_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="file" name="pdf_file" accept=".pdf" required>
                <button type="submit" class="upload-btn">Tải lên</button>
            </form>
        </div>
        <?php endif; ?>
        
        <div class="books-container">
            <?php if (empty($books)): ?>
                <div class="no-books">
                    <h3>Chưa có tài liệu Edufly nào trong thư viện</h3>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 1): ?>
                    <p>Hãy thêm tài liệu Edufly đầu tiên!</p>
                    <?php else: ?>
                    <p>Tài liệu Edufly sẽ sớm được cập nhật bởi quản trị viên.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <div class="book-image">
                            <?php if (!empty($book['image'])): ?>
                                <img src="<?php echo htmlspecialchars($book['image']); ?>" alt="<?php echo htmlspecialchars($book['product_name']); ?>">
                            <?php else: ?>
                                <i class="fas fa-file-pdf"></i>
                            <?php endif; ?>
                        </div>
                        <div class="book-info">
                            <h3 class="book-title"><?php echo htmlspecialchars($book['product_name']); ?></h3>
                            <div class="book-meta">
                                <span>Loại: <?php echo htmlspecialchars($book['type_name']); ?></span>
                            </div>
                            <div class="book-actions">
                                <a href="view_edufly_pdf.php?id=<?php echo $book['product_id']; ?>" class="view-btn">
                                    <i class="fas fa-book-open"></i> Đọc tài liệu
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Xử lý lỗi hình ảnh
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.book-image img');
            
            images.forEach(img => {
                img.onerror = function() {
                    // Ẩn hình ảnh lỗi
                    this.style.display = 'none';
                    
                    // Hiển thị icon thay thế
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-file-pdf';
                    icon.style.fontSize = '80px';
                    icon.style.opacity = '0.8';
                    
                    // Thêm icon vào container
                    this.parentNode.appendChild(icon);
                };
            });
        });
    </script>
</body>
</html>