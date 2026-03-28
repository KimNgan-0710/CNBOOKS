<?php
require '../connect.php';

// Thiết lập tiêu đề trang
$page_title = 'Thêm Người Dùng Mới';

// Xử lý form khi được gửi
$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = isset($_POST['role']) ? 1 : 0;
    
    // Kiểm tra dữ liệu
    if (empty($username) || empty($fullname) || empty($phone) || empty($email) || empty($password)) {
        $message = 'Vui lòng điền đầy đủ thông tin!';
        $messageType = 'error';
    } else {
        try {
            // Kiểm tra username và email đã tồn tại chưa
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();
            
            $checkEmailStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $checkEmailStmt->bindParam(':email', $email);
            $checkEmailStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $message = 'Tên đăng nhập đã tồn tại!';
                $messageType = 'error';
            } elseif ($checkEmailStmt->rowCount() > 0) {
                $message = 'Email đã được sử dụng!';
                $messageType = 'error';
            } else {
                // Mã hóa mật khẩu
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Thêm người dùng mới
                $insertStmt = $pdo->prepare("INSERT INTO users (username, fullname, phone, email, password, role) 
                                           VALUES (:username, :fullname, :phone, :email, :password, :role)");
                
                $insertStmt->bindParam(':username', $username);
                $insertStmt->bindParam(':fullname', $fullname);
                $insertStmt->bindParam(':phone', $phone);
                $insertStmt->bindParam(':email', $email);
                $insertStmt->bindParam(':password', $hashedPassword);
                $insertStmt->bindParam(':role', $role);
                
                if ($insertStmt->execute()) {
                    $message = 'Thêm người dùng mới thành công!';
                    $messageType = 'success';
                    
                    // Xóa dữ liệu form sau khi thêm thành công
                    $username = $fullname = $phone = $email = $password = '';
                    $role = 0;
                } else {
                    $message = 'Có lỗi xảy ra khi thêm người dùng mới!';
                    $messageType = 'error';
                }
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
    
    .checkbox-group {
        margin-bottom: 20px;
    }
    
    .checkbox-group label {
        display: inline-block;
        margin-left: 10px;
        color: #2c3e50;
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
    
    .required-note {
        font-size: 12px;
        color: #e74c3c;
        margin-bottom: 15px;
    }
';

// Bắt đầu nội dung trang
ob_start();
?>

<div class="content-wrapper">
    <div class="header">
        <h1>Thêm Người Dùng Mới</h1>
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <p class="required-note">* Thông tin bắt buộc</p>
    
    <form action="" method="POST">
        <div class="form-group">
            <label for="username">Tên đăng nhập <span style="color: red;">*</span></label>
            <input type="text" id="username" name="username" value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" required>
        </div>
        
        <div class="form-group">
            <label for="fullname">Họ và tên <span style="color: red;">*</span></label>
            <input type="text" id="fullname" name="fullname" value="<?= isset($fullname) ? htmlspecialchars($fullname) : '' ?>" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Số điện thoại <span style="color: red;">*</span></label>
            <input type="tel" id="phone" name="phone" value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>" required pattern="[0-9]{10}" title="Vui lòng nhập số điện thoại 10 chữ số">
        </div>
        
        <div class="form-group">
            <label for="email">Email <span style="color: red;">*</span></label>
            <input type="email" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Mật khẩu <span style="color: red;">*</span></label>
            <input type="password" id="password" name="password" required minlength="6">
        </div>
        
        <div class="checkbox-group">
            <input type="checkbox" id="role" name="role" <?= isset($role) && $role == 1 ? 'checked' : '' ?>>
            <label for="role">Quản trị viên</label>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Thêm Người Dùng</button>
            <a href="analytics_users.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay Lại</a>
        </div>
    </form>
</div>

<?php
// Kết thúc nội dung trang
$page_content = ob_get_clean();

// Nhúng layout
include 'admin_layout.php';
?>