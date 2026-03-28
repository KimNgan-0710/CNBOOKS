<?php
// Không cần gọi session_start() ở đây vì đã được gọi trong header.php
include 'header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài Liệu Edufly - CNBooks</title>
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
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .book-image {
            width: 100%;
            height: 350px;
            object-fit: cover;
        }

        .book-info {
            padding: 15px;
        }

        .book-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .book-author {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .book-description {
            font-size: 14px;
            color: #777;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .book-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .download-btn {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .download-btn:hover {
            background: #3a7bc8;
        }

        .book-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #f39c12;
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
        
        <div class="books-container">
            <!-- Book 1 -->
            <div class="book-card">
                <img src="https://edufly.edu.vn/wp-content/uploads/2023/04/Sach-giao-khoa-Tieng-Anh-lop-10-Global-Success.jpg" alt="Sách Tiếng Anh 10" class="book-image">
                <div class="book-info">
                    <h3 class="book-title">Sách Giáo Khoa Tiếng Anh 10 - Global Success</h3>
                    <p class="book-author">Tác giả: Bộ Giáo Dục & Đào Tạo</p>
                    <p class="book-description">Sách giáo khoa Tiếng Anh 10 - Global Success cung cấp kiến thức và bài tập theo chương trình mới, giúp học sinh phát triển 4 kỹ năng nghe, nói, đọc, viết.</p>
                    <div class="book-actions">
                        <a href="#" class="download-btn"><i class="fas fa-download"></i> Tải xuống</a>
                        <div class="book-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Book 2 -->
            <div class="book-card">
                <img src="https://edufly.edu.vn/wp-content/uploads/2022/08/sach-giao-khoa-toan-10.jpg" alt="Sách Toán 10" class="book-image">
                <div class="book-info">
                    <h3 class="book-title">Sách Giáo Khoa Toán 10 - Cánh Diều</h3>
                    <p class="book-author">Tác giả: Bộ Giáo Dục & Đào Tạo</p>
                    <p class="book-description">Sách giáo khoa Toán 10 - Cánh Diều được biên soạn theo chương trình GDPT mới, giúp học sinh phát triển tư duy logic và kỹ năng giải quyết vấn đề.</p>
                    <div class="book-actions">
                        <a href="#" class="download-btn"><i class="fas fa-download"></i> Tải xuống</a>
                        <div class="book-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Book 3 -->
            <div class="book-card">
                <img src="https://edufly.edu.vn/wp-content/uploads/2023/04/Sach-giao-khoa-Vat-ly-10-Canh-dieu.jpg" alt="Sách Vật Lý 10" class="book-image">
                <div class="book-info">
                    <h3 class="book-title">Sách Giáo Khoa Vật Lý 10 - Cánh Diều</h3>
                    <p class="book-author">Tác giả: Bộ Giáo Dục & Đào Tạo</p>
                    <p class="book-description">Sách giáo khoa Vật Lý 10 - Cánh Diều cung cấp kiến thức cơ bản và nâng cao về các hiện tượng vật lý, giúp học sinh hiểu rõ các quy luật tự nhiên.</p>
                    <div class="book-actions">
                        <a href="#" class="download-btn"><i class="fas fa-download"></i> Tải xuống</a>
                        <div class="book-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Book 4 -->
            <div class="book-card">
                <img src="https://edufly.edu.vn/wp-content/uploads/2023/04/Sach-giao-khoa-Hoa-hoc-10-Canh-dieu.jpg" alt="Sách Hóa Học 10" class="book-image">
                <div class="book-info">
                    <h3 class="book-title">Sách Giáo Khoa Hóa Học 10 - Cánh Diều</h3>
                    <p class="book-author">Tác giả: Bộ Giáo Dục & Đào Tạo</p>
                    <p class="book-description">Sách giáo khoa Hóa Học 10 - Cánh Diều giúp học sinh nắm vững kiến thức cơ bản về hóa học, các phản ứng hóa học và ứng dụng trong đời sống.</p>
                    <div class="book-actions">
                        <a href="#" class="download-btn"><i class="fas fa-download"></i> Tải xuống</a>
                        <div class="book-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Book 5 -->
            <div class="book-card">
                <img src="https://edufly.edu.vn/wp-content/uploads/2023/04/Sach-giao-khoa-Sinh-hoc-10-Canh-dieu.jpg" alt="Sách Sinh Học 10" class="book-image">
                <div class="book-info">
                    <h3 class="book-title">Sách Giáo Khoa Sinh Học 10 - Cánh Diều</h3>
                    <p class="book-author">Tác giả: Bộ Giáo Dục & Đào Tạo</p>
                    <p class="book-description">Sách giáo khoa Sinh Học 10 - Cánh Diều cung cấp kiến thức về thế giới sinh vật, cấu trúc tế bào và các quá trình sinh học cơ bản.</p>
                    <div class="book-actions">
                        <a href="#" class="download-btn"><i class="fas fa-download"></i> Tải xuống</a>
                        <div class="book-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <i class="far fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Book 6 -->
            <div class="book-card">
                <img src="https://edufly.edu.vn/wp-content/uploads/2023/04/Sach-giao-khoa-Lich-su-10-Canh-dieu.jpg" alt="Sách Lịch Sử 10" class="book-image">
                <div class="book-info">
                    <h3 class="book-title">Sách Giáo Khoa Lịch Sử 10 - Cánh Diều</h3>
                    <p class="book-author">Tác giả: Bộ Giáo Dục & Đào Tạo</p>
                    <p class="book-description">Sách giáo khoa Lịch Sử 10 - Cánh Diều giúp học sinh hiểu rõ về lịch sử thế giới và Việt Nam từ thời kỳ nguyên thủy đến thế kỷ XIX.</p>
                    <div class="book-actions">
                        <a href="#" class="download-btn"><i class="fas fa-download"></i> Tải xuống</a>
                        <div class="book-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>