<?php
require '../connect.php';

// Thiết lập tiêu đề trang
$page_title = 'Chỉnh Sửa Bài Đăng';

// Kiểm tra ID bài đăng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID bài đăng không hợp lệ!'); window.location.href='posts_management.php';</script>";
    exit;
}

$postId = (int)$_GET['id'];

// Lấy thông tin bài đăng
try {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE post_id = :id");
    $stmt->bindParam(':id', $postId);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        echo "<script>alert('Không tìm thấy bài đăng!'); window.location.href='posts_management.php';</script>";
        exit;
    }
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Lấy danh sách người dùng
try {
    $userStmt = $pdo->query("SELECT DISTINCT user_name FROM posts ORDER BY user_name");
    $users = $userStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $users = [];
}

// Xử lý form khi được gửi
$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $image = trim($_POST['image']);
    $userName = trim($_POST['user_name']);
    
    // Kiểm tra dữ liệu
    if (empty($image) || empty($userName)) {
        $message = 'Vui lòng điền đầy đủ thông tin!';
        $messageType = 'error';
    } else {
        try {
            // Cập nhật bài đăng
            $stmt = $pdo->prepare("UPDATE posts SET image = :image, user_name = :user_name WHERE post_id = :id");
            
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':user_name', $userName);
            $stmt->bindParam(':id', $postId);
            
            if ($stmt->execute()) {
                $message = 'Cập nhật bài đăng thành công!';
                $messageType = 'success';
                
                // Cập nhật thông tin bài đăng hiển thị
                $post['image'] = $image;
                $post['user_name'] = $userName;
            } else {
                $message = 'Có lỗi xảy ra khi cập nhật bài đăng!';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// CSS bổ sung cho trang này
$extra_css = '
    .content-wrapper {
        max-width: 800px;
        margin: 0 auto;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }
    
    .header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    
    .header h1 {
        color: #2c3e50;
        font-size: 24px;
    }
    
    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }
    
    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #2c3e50;
        font-weight: 500;
    }
    
    .form-group input, .form-group select {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        transition: border-color 0.3s;
    }
    
    .form-group input:focus, .form-group select:focus {
        border-color: #3498db;
        outline: none;
    }
    
    .btn {
        display: inline-block;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        text-align: center;
        transition: background-color 0.3s;
        border: none;
    }
    
    .btn-primary {
        background-color: #3498db;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #2980b9;
    }
    
    .btn-secondary {
        background-color: #7f8c8d;
        color: white;
        text-decoration: none;
        margin-left: 10px;
    }
    
    .btn-secondary:hover {
        background-color: #6c7a89;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 30px;
    }
    
    .preview-container {
        margin-top: 20px;
        text-align: center;
    }
    
    .image-preview {
        max-width: 100%;
        max-height: 300px;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 5px;
    }
    
    .user-input-container {
        position: relative;
    }
    
    .user-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 5px 5px;
        max-height: 150px;
        overflow-y: auto;
        z-index: 10;
        display: none;
    }
    
    .user-suggestion {
        padding: 8px 15px;
        cursor: pointer;
    }
    
    .user-suggestion:hover {
        background-color: #f5f5f5;
    }
';

// JavaScript bổ sung cho trang này
$extra_js = '
    function previewImage(url) {
        const preview = document.getElementById("imagePreview");
        if (url) {
            preview.src = url;
            preview.style.display = "inline-block";
        } else {
            preview.style.display = "none";
        }
    }
    
    // Xử lý gợi ý người dùng
    window.onload = function() {
        const userInput = document.getElementById("user_name");
        const userSuggestions = document.getElementById("userSuggestions");
        const suggestions = ' . json_encode($users) . ';
        
        userInput.addEventListener("input", function() {
            const value = this.value.toLowerCase();
            let html = "";
            
            if (value.length > 0) {
                const filteredSuggestions = suggestions.filter(suggestion => 
                    suggestion.toLowerCase().includes(value)
                );
                
                if (filteredSuggestions.length > 0) {
                    filteredSuggestions.forEach(suggestion => {
                        html += `<div class="user-suggestion" onclick="selectUser(\'${suggestion}\')">${suggestion}</div>`;
                    });
                    userSuggestions.innerHTML = html;
                    userSuggestions.style.display = "block";
                } else {
                    userSuggestions.style.display = "none";
                }
            } else {
                userSuggestions.style.display = "none";
            }
        });
        
        // Ẩn gợi ý khi click ra ngoài
        document.addEventListener("click", function(e) {
            if (e.target !== userInput && e.target.className !== "user-suggestion") {
                userSuggestions.style.display = "none";
            }
        });
    };
    
    function selectUser(username) {
        document.getElementById("user_name").value = username;
        document.getElementById("userSuggestions").style.display = "none";
    }
';

// Bắt đầu nội dung trang
ob_start();
?>

<div class="content-wrapper">
    <div class="header">
        <h1>Chỉnh Sửa Bài Đăng</h1>
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <div class="form-group">
            <label for="image">URL Hình Ảnh <span style="color: red;">*</span></label>
            <input type="url" id="image" name="image" value="<?= isset($post['image']) ? htmlspecialchars($post['image']) : '' ?>" required onchange="previewImage(this.value)">
            <div class="preview-container">
                <img id="imagePreview" class="image-preview" src="<?= isset($post['image']) ? htmlspecialchars($post['image']) : '../images/no-image.jpg' ?>" alt="Xem trước hình ảnh">
            </div>
        </div>
        
        <div class="form-group">
            <label for="user_name">Tên Người Dùng <span style="color: red;">*</span></label>
            <div class="user-input-container">
                <input type="text" id="user_name" name="user_name" value="<?= isset($post['user_name']) ? htmlspecialchars($post['user_name']) : '' ?>" required>
                <div id="userSuggestions" class="user-suggestions"></div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu Thay Đổi</button>
            <a href="posts_management.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay Lại</a>
        </div>
    </form>
</div>

<?php
// Kết thúc nội dung trang
$page_content = ob_get_clean();

// Nhúng layout
include 'admin_layout.php';
?>