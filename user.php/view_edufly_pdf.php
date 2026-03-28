<?php
// Không cần gọi session_start() ở đây vì đã được gọi trong header.php
include 'header.php';
require_once 'connect.php';

// Kiểm tra xem có ID sách được truyền vào không
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Nếu không có ID hợp lệ, chuyển hướng về trang danh sách sách
    header('Location: pdf_books.php');
    exit;
}

$product_id = (int)$_GET['id'];

// Lấy thông tin sách từ database
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ? AND type_name = ?");
$stmt->execute([$product_id, "Tài liệu toán học"]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy sách, chuyển hướng về trang danh sách sách
if (!$book) {
    header('Location: pdf_books.php');
    exit;
}

// Đường dẫn đến file PDF
$pdf_path = 'pdf_books/' . $book['file_pdf'];

// Kiểm tra xem file PDF có tồn tại không
$pdf_exists = file_exists($pdf_path);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['product_name']); ?> - CNBooks</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            margin-top: 100px; /* Để không bị che bởi header */
            padding: 0;
        }

        .container {
            width: 95%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .book-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .book-image {
            width: 180px;
            height: 180px;
            background-color: #e9f0f7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #4a90e2;
            border-radius: 10px;
            margin-right: 30px;
            overflow: hidden;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            border: 5px solid white;
        }

        .book-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .book-image:hover img {
            transform: scale(1.08);
        }
        
        .book-image i {
            font-size: 80px;
            opacity: 0.8;
        }

        .book-info {
            flex-grow: 1;
        }

        .book-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            position: relative;
            padding-bottom: 12px;
            line-height: 1.3;
        }
        
        .book-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background-color: #4a90e2;
            border-radius: 4px;
        }

        .book-description {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
            line-height: 1.6;
            max-height: 200px;
            overflow-y: auto;
            padding-right: 10px;
            text-align: justify;
        }
        
        .book-description::-webkit-scrollbar {
            width: 5px;
        }
        
        .book-description::-webkit-scrollbar-thumb {
            background-color: #ccc;
            border-radius: 5px;
        }

        .book-meta {
            font-size: 14px;
            color: #888;
            display: flex;
            gap: 20px;
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            border-left: 4px solid #4a90e2;
            margin-bottom: 20px;
        }
        
        .book-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .book-meta span:before {
            content: '•';
            color: #4a90e2;
            font-weight: bold;
        }

        .back-btn {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-right: 10px;
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #3a7bc8;
            box-shadow: 0 6px 12px rgba(74, 144, 226, 0.4);
            transform: translateY(-2px);
        }

        .pdf-container {
            width: 100%;
            height: 800px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            background: white;
            margin-top: 30px;
            position: relative;
            border: 1px solid #e0e0e0;
        }
        
        .pdf-container:before {
            content: 'Đang tải tài liệu...';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #666;
            font-size: 18px;
            z-index: 0;
        }

        .pdf-viewer {
            width: 100%;
            height: 100%;
            border: none;
            position: relative;
            z-index: 1;
            background: white;
        }

        .no-pdf {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .book-header {
                flex-direction: column;
                text-align: center;
            }

            .book-image {
                margin-right: 0;
                margin-bottom: 20px;
            }

            .pdf-container {
                height: 500px;
            }
        }

        .pdf-page-canvas { display: block; margin: 0 auto; background: #fff; box-shadow: none; border: none; }
        .pdf-container { background: #fff; padding: 0; border: none; box-shadow: none; position: relative; min-height: 200px; }
        .loading-text { 
            position: absolute; 
            top: 50%; left: 50%; 
            transform: translate(-50%, -50%); 
            color: #666; 
            font-size: 18px; 
            z-index: 10; 
            text-align: center;
        }

        #toolbarContainer {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="book-header">
            <div class="book-image">
                <?php if (!empty($book['image'])): ?>
                    <img src="<?php echo htmlspecialchars($book['image']); ?>" alt="<?php echo htmlspecialchars($book['product_name']); ?>">
                <?php else: ?>
                    <i class="fas fa-file-pdf"></i>
                <?php endif; ?>
            </div>
            <div class="book-info">
                <h1 class="book-title"><?php echo htmlspecialchars($book['product_name']); ?></h1>
                <p class="book-description"><?php echo nl2br(htmlspecialchars($book['discription'])); ?></p>
                <div class="book-meta">
                    <span>Loại: <?php echo htmlspecialchars($book['type_name']); ?></span>
                </div>
            </div>
            <a href="pdf_books.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <?php if ($pdf_exists): ?>
            <div class="pdf-container" style="box-shadow:none; border:none; background:#fff;">
                <iframe 
                    src="<?php echo htmlspecialchars($pdf_path); ?>" 
                    class="pdf-viewer" 
                    style="width:100%;height:800px;border:none;box-shadow:none;background:#fff;"
                    allowfullscreen>
                </iframe>
            </div>
        <?php else: ?>
            <div class="no-pdf">
                <h2>Rất tiếc, file PDF của tài liệu này không tồn tại.</h2>
                <p>Vui lòng liên hệ quản trị viên để được hỗ trợ.</p>
                <a href="pdf_books.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Quay lại danh sách tài liệu
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Xử lý lỗi hình ảnh
        document.addEventListener('DOMContentLoaded', function() {
            const bookImage = document.querySelector('.book-image img');
            
            if (bookImage) {
                bookImage.onerror = function() {
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
            }
        });
    </script>
</body>
</html>