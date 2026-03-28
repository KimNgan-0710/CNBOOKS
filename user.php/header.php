<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNBooks</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        a {
            text-decoration: none;
            color: white;
        }

        header {
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(to right, #6ec1e4, #4a90e2);
            text-align: center;
            padding: 15px 0;
            z-index: 1000;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            margin: auto;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }


        .logo i {
            font-size: 28px;
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 20px;
        }

        .nav-links li {
            position: relative;
            
        }

        .nav-links a {
            text-decoration: none;
            color: white;
            font-size: 16px;
            padding: 10px 15px;
            transition: 0.3s;
            border-radius: 8px;
            
        }

        .nav-links a:hover {
            text-decoration: none;
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
            
        }

        
        .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            display: none;
            flex-direction: column;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-top: 10px;
            text-align: left;
        }

        .nav-links li:hover .dropdown {
            display: flex;
            
        }

        .dropdown a {
            color: #4a90e2;
            padding: 12px 15px;
            width: 160px;
            display: block;
            transition: 0.3s;
        }

        .dropdown a:hover {
            background: #4a90e2;
            color: white;
            
        }

        .auth-buttons {
            display: flex;
            gap: 10px;
        }

        .auth-buttons a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 6px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .auth-buttons a:hover {
            background: rgba(255, 255, 255, 0.3);
            display: flex;
            text-decoration: none;
            color: #fff;
            transform: translateY(-5px);
           /* margin-right: 130px;*/
            /* Đẩy sang trái */
        }
       
    </style>
</head>

<body>

    <header class="header">
        <div class="nav-container">
            <div class="logo" id="logo-reload">
                <i class="fas fa-book-reader"></i> CNBooks
            </div>
            <ul class="nav-links">
                <li><a href="index.php"><i class="fas fa-home"></i> Trang chủ</a></li>
                <li><a href="./post/post.php"><i class="fas fa-compass"></i> Lướt</a></li>
                <li>
                    <a href="#"><i class="fas fa-book"></i> Sách ▼</a>
                    <div class="dropdown">
                        <a href="readoldstory.php">📖 Cổ tích</a>
                        <a href="readscience.php">📚 Khoa học</a>
                        <a href="readhistory.php">📄 Tư liệu</a>
                        <a href="pdf_books.php">📘 Tài Liệu Toán học</a>
                    </div>
                </li>
                <li><a href="product.php"><i class="fas fa-shopping-cart"></i> Mua sắm</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 0): ?>
                <li><a href="contact.php"><i class="fas fa-envelope"></i> Liên hệ</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 1): ?>
                    <li><a href="./sup/index.php"><i class="fas fa-shopping-cart"></i> Quản Lý</a></li>
                <?php endif; ?>
            </ul>
            <div class="auth-buttons">
                <?php if (isset($_SESSION['username'])) : ?>
                    <a href="user_profile.php" style="color: white; font-weight: bold;">
                        <i class="fas fa-user-circle"></i> Hi, <?= htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                <?php else : ?>
                    <a href="login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập/Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
        <!-- header.php -->
        <div id="cart-icon" style="position: fixed; top: 20px; right: 30px; cursor: pointer; z-index: 999;">
            <i class="fas fa-shopping-cart"></i>
            <span id="cart-count">0</span>
        </div>

        <div id="cart-box" style="display: none; position: fixed; top: 70px; right: 30px; width: 350px; max-height: 450px; overflow-y: auto; z-index: 999;">
            <h4>Giỏ hàng của bạn</h4>
            <div id="cart-items"></div>
        </div>

        
    </header>

    <script src="cart.js"></script>
    <script>
        // Reload trang khi click vào logo
        document.addEventListener('DOMContentLoaded', function() {
            var logo = document.getElementById('logo-reload');
            if (logo) {
                logo.style.cursor = 'pointer';
                logo.onclick = function() {
                    window.location.reload();
                };
            }
        });
    </script>
</body>

</html>