<?php
session_start();
require 'connect.php';
require 'like.php';

// Kiểm tra thông tin đăng nhập từ session
$loggedInUsername = '';
$isLoggedIn = false;

if (isset($_SESSION['user_id'])) {
    // Lấy thông tin người dùng từ database
    $stmt = $pdo->prepare("SELECT username, fullname FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        $loggedInUsername = !empty($user['fullname']) ? $user['fullname'] : $user['username'];
        $isLoggedIn = true;
    }
}

// Lấy danh sách bài viết từ database
$posts = $pdo->query("SELECT * FROM posts ORDER BY post_id ")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lướt With CNBOOKS</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #ffe4e1;
            font-family: 'Comic Sans MS', cursive, sans-serif;
        }

        .container {
            width: 100%;
            max-width: 1300px; /* Tăng chiều rộng tối đa */
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 cột bằng nhau */
            grid-gap: 40px; /* Tăng khoảng cách giữa các cột từ 20px lên 40px */
            box-sizing: border-box;
            padding: 0 30px; /* Tăng padding cho container */
        }

        .post-box {
            background: white;
            border-radius: 15px; /* Tăng độ bo tròn góc */
            padding: 18px; /* Tăng padding để tạo không gian thoáng hơn */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); /* Tăng đổ bóng để nổi bật hơn */
            text-align: center;
            width: 100%;
            position: relative;
            margin-bottom: 30px; /* Tăng khoảng cách giữa các hàng */
            box-sizing: border-box;
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* Thêm hiệu ứng chuyển động */
            overflow: hidden; /* Đảm bảo nội dung không tràn ra ngoài */
        }
        
        /* Thêm hiệu ứng hover cho post-box */
        .post-box:hover {
            transform: translateY(-5px); /* Nâng nhẹ khi di chuột qua */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); /* Tăng đổ bóng khi hover */
        }
        
        /* Thêm hiệu ứng hover cho post-box */
        .post-box:hover {
            transform: translateY(-5px); /* Nâng nhẹ khi di chuột qua */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); /* Tăng đổ bóng khi hover */
        }

        .post-box img {
            width: 100%;
            
            height: 450px; /* Tăng chiều cao ảnh từ 350px lên 450px */
            object-fit: cover; /* Đảm bảo ảnh không bị méo */
            margin-top: 0;
            transition: transform 0.3s ease; /* Thêm hiệu ứng chuyển động mượt mà */
            /*box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Thêm đổ bóng để tạo chiều sâu */
        }
        
        /* Thêm hiệu ứng hover cho ảnh */
        /*.post-box img:hover {
            transform: scale(1.02); /* Phóng to nhẹ khi di chuột qua 
        }*/
        
        /* Áp dụng style riêng cho ảnh bài viết, không áp dụng cho logo */
        .post-box > img {
            margin-top: 0;
            z-index: 1;
        }
        
        /* Media queries để đảm bảo responsive */
        @media (max-width: 1200px) {
            .container {
                grid-gap: 30px; /* Giảm khoảng cách một chút trên màn hình nhỏ hơn */
                padding: 0 20px;
            }
        }
        
        @media (max-width: 992px) {
            .container {
                grid-template-columns: repeat(2, 1fr); /* 2 cột trên màn hình trung bình */
                grid-gap: 25px; /* Giảm khoảng cách hơn nữa */
            }
        }
        
        @media (max-width: 576px) {
            .container {
                grid-template-columns: 1fr; /* 1 cột trên màn hình nhỏ */
                grid-gap: 20px;
                padding: 0 15px;
            }
        }

        .logo-container {
            text-align: center;
            position: relative;
            height: 50px; /* Chiều cao container */
            margin-bottom: 15px; /* Khoảng cách với nội dung bên dưới */
            z-index: 5;
        }
        
        .icon-img {
            width: 90px !important; /* Kích thước logo */
            height: 90px !important;
            object-fit: contain;
            /*border-radius: 50% !important;
            border: 4px solid #ffb6c1; /* Viền màu hồng */
            position: absolute;
            /*top: -45px; /* Đặt logo ở vị trí cao hơn */
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15); /* Đổ bóng */
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* Hiệu ứng */
        }
        
        /* Áp dụng style riêng cho ảnh bài viết, không áp dụng cho logo */
        .post-box > img {
            margin-top: 50px;
            z-index: 1;
        }
        
        /* Thêm hiệu ứng hover cho logo */
        .icon-img:hover {
            transform: translateX(-50%) scale(1.05); /* Phóng to nhẹ khi hover */
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }

        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding: 10px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
        }

        .like-btn {
            background: none;
            border: none;
            font-size: 24px; /* Tăng kích thước icon */
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 30px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .like-btn:hover {
            background-color: rgba(255, 0, 0, 0.1); /* Thêm hiệu ứng hover */
            transform: scale(1.05);
        }
        
        .like-btn .heart-icon {
            font-size: 24px;
            transition: transform 0.3s ease;
            color: red;
        }
        
        
        
        .like-count {
            font-weight: bold;
            margin-left: 5px;
            color: #333;
        }
        
        /* Hiệu ứng nhảy khi nhấn nút */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .like-btn.pulse .heart-icon {
            animation: pulse 0.3s ease;
        }

        .comment-section {
            margin-top: 15px;
            text-align: left;
            max-height: 300px; /* Giới hạn chiều cao tối đa */
            overflow-y: auto; /* Thêm thanh cuộn nếu nội dung vượt quá chiều cao */
            padding-right: 5px; /* Thêm padding bên phải để tránh nội dung bị che bởi thanh cuộn */
        }

        /* Tùy chỉnh thanh cuộn */
        .comment-section::-webkit-scrollbar {
            width: 6px;
        }
        
        .comment-section::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .comment-section::-webkit-scrollbar-thumb {
            background: #ffb6c1;
            border-radius: 10px;
        }
        
        .comment-section::-webkit-scrollbar-thumb:hover {
            background: #ff8da1;
        }

        .comment-input {
            width: 100%;
            padding: 12px 15px; /* Tăng padding để dễ nhập liệu */
            border-radius: 25px; /* Bo tròn góc nhiều hơn */
            border: 2px solid #ffb6c1;
            font-size: 14px;
            transition: all 0.3s ease;
            outline: none; /* Loại bỏ viền khi focus */
            box-sizing: border-box; /* Đảm bảo padding không làm tăng kích thước */
            margin-bottom: 10px; /* Thêm khoảng cách với phần comments */
        }
        
        .comment-input:focus {
            border-color: #ff8da1;
            box-shadow: 0 0 8px rgba(255, 182, 193, 0.5);
        }

        .comments {
            max-height: 220px; /* Giới hạn chiều cao của phần comments */
            overflow-y: auto; /* Thêm thanh cuộn nếu cần */
        }

        .comments p {
            background: #fff0f5;
            padding: 10px 15px;
            border-radius: 15px;
            margin: 10px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            font-size: 14px;
            line-height: 1.4;
            word-wrap: break-word; /* Đảm bảo văn bản dài sẽ được ngắt xuống dòng */
        }
        
        .comments p strong {
            color: #ff6b81;
            margin-right: 5px;
        }
        
        /* Thêm hiệu ứng cho comment mới */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
	    .username-input, .comment-input {
            width: 100%;
            padding: 12px 15px; /* Tăng padding để dễ nhập liệu */
            border-radius: 25px; /* Bo tròn góc nhiều hơn */
            border: 2px solid #ffb6c1;
            font-size: 14px;
            margin-bottom: 10px; /* Thêm khoảng cách với phần comments */
            transition: all 0.3s ease;
            outline: none; /* Loại bỏ viền khi focus */
            box-sizing: border-box; /* Đảm bảo padding không làm tăng kích thước */
        }
        
        .username-input {
            border-color: #ffd1dc;
            background-color: #fff9fa;
        }
        
        .username-input[readonly] {
            background-color: #f8f8f8;
            border-color: #ddd;
            color: #666;
            cursor: not-allowed;
            opacity: 0.8;
        }
        
        .username-input:focus, .comment-input:focus {
            border-color: #ff8da1;
            box-shadow: 0 0 8px rgba(255, 182, 193, 0.5);
        }
    </style>
</head>

<body>
    <h4>Chúng tớ có thể không chữa lành được những vết thương trong lòng của cậu được.</h4>
        <h4 >Nhưng ít ra, chúng tớ chưa từng làm cậu bị thương ^^ </h4>
    <div class="container">
        <?php foreach ($posts as $post): ?>
            <div class="post-box">
                <div class="logo-container">
                    <img src="./image/icon.jpg" alt="Icon đáng yêu" class="icon-img">
                </div>
                <img src="<?php echo $post['image']; ?>" alt="Post Image" class="post-image">
                <div class="post-actions">
                    <?php
                        // Kiểm tra xem người dùng hiện tại đã like bài viết này chưa
                        $user_identifier = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SERVER['REMOTE_ADDR'];
                        $checkLikeStmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ? AND username = ? AND likes = 1 AND comment = ''");
                        $checkLikeStmt->execute([$post['post_id'], $user_identifier]);
                        $hasLiked = $checkLikeStmt->fetchColumn() > 0;
                        
                        // Đếm tổng số lượt thích
                        $likeStmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ? AND likes = 1");
                        $likeStmt->execute([$post['post_id']]);
                        $likeCount = $likeStmt->fetchColumn();
                    ?>
                    <button class="like-btn <?php echo $hasLiked ? 'liked' : ''; ?>" data-id="<?php echo $post['post_id']; ?>">
                        <span class="heart-icon">❤️</span>
                        <span class="like-count"><?php echo $likeCount; ?></span>
                    </button>

                </div>
                <div class="comment-section">
                    <div class="comment-form">
                        <input type="text" class="username-input" placeholder="Tên của bạn..." id="username-<?php echo $post['post_id']; ?>" value="<?php echo htmlspecialchars($loggedInUsername); ?>" <?php echo $isLoggedIn ? 'readonly' : ''; ?>>
                        <input type="text" class="comment-input" placeholder="Viết bình luận..." onkeypress="addComment(event, <?php echo $post['post_id']; ?>)">
                    </div>
                    <div class="comments" id="comments-<?php echo $post['post_id']; ?>">
                        <?php
                        $comments = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY post_id DESC");
                        $comments->execute([$post['post_id']]);
                        $comments = $comments->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($comments as $comment) {
                            echo "<p><strong>{$comment['username']}:</strong> {$comment['comment']}</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const likeButtons = document.querySelectorAll(".like-btn");

            likeButtons.forEach(button => {
                button.addEventListener("click", function() {
                    let countSpan = this.querySelector(".like-count");
                    let post_id = this.getAttribute("data-id");

                    fetch("like.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "post_id=" + post_id
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                countSpan.textContent = data.likes; // Cập nhật số tim mới
                            }
                        })
                        .catch(error => console.error("Lỗi:", error));
                });
            });
        });

        function addComment(event, postId) {
            if (event.key === 'Enter') {
                let input = event.target;
                let comment = input.value.trim();
                if (comment === '') return;
                
                // Lấy username từ input
                let usernameInput = document.getElementById('username-' + postId);
                let username = usernameInput.value.trim();

                // Hiển thị hiệu ứng đang xử lý
                input.disabled = true;
                input.style.opacity = "0.7";
                
                fetch('comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'post_id=' + postId + '&comment=' + encodeURIComponent(comment) + '&username=' + encodeURIComponent(username)
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Bỏ hiệu ứng đang xử lý
                        input.disabled = false;
                        input.style.opacity = "1";
                        
                        if (data.success) {
                            // Lấy container chứa comments
                            let commentsDiv = document.getElementById('comments-' + postId);
                            
                            // Tạo phần tử mới cho comment
                            let newComment = document.createElement('p');
                            newComment.innerHTML = `<strong>${data.username}:</strong> ${data.comment}`;
                            
                            // Thêm hiệu ứng cho comment mới
                            newComment.style.animation = "fadeIn 0.5s";
                            
                            // Thêm comment vào đầu danh sách
                            if (commentsDiv.firstChild) {
                                commentsDiv.insertBefore(newComment, commentsDiv.firstChild);
                            } else {
                                commentsDiv.appendChild(newComment);
                            }
                            
                            // Xóa nội dung trong ô nhập
                            input.value = '';
                            
                            // Cuộn đến comment mới nếu cần
                            newComment.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        } else {
                            alert('Có lỗi xảy ra, vui lòng thử lại!');
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi:', error);
                        input.disabled = false;
                        input.style.opacity = "1";
                        alert('Có lỗi khi gửi bình luận, vui lòng thử lại sau!');
                    });
            }
        }
    </script>
</body>

</html>