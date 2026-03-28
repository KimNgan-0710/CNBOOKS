<?php
require '../connect.php';

// Thiết lập tiêu đề trang
$page_title = 'Chỉnh Sửa Người Dùng';

// Kiểm tra ID người dùng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID người dùng không hợp lệ!'); window.location.href='analytics_users.php';</script>";
    exit;
}

$userId = (int)$_GET['id'];

// Lấy thông tin người dùng
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<script>alert('Không tìm thấy người dùng!'); window.location.href='analytics_users.php';</script>";
        exit;
    }
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Xử lý form khi được gửi
$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $role = isset($_POST['role']) ? 1 : 0;
    
    // Kiểm tra mật khẩu mới (nếu có)
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];
    
    // Kiểm tra dữ liệu
    if (empty($username) || empty($fullname) || empty($phone) || empty($email)) {
        $message = 'Vui lòng điền đầy đủ thông tin!';
        $messageType = 'error';
    } else {
        try {
            // Kiểm tra username và email đã tồn tại chưa (trừ người dùng hiện tại)
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
            $checkStmt->bindParam(':username', $username);
            $checkStmt->bindParam(':id', $userId);
            $checkStmt->execute();
            
            $checkEmailStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $checkEmailStmt->bindParam(':email', $email);
            $checkEmailStmt->bindParam(':id', $userId);
            $checkEmailStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $message = 'Tên đăng nhập đã tồn tại!';
                $messageType = 'error';
            } elseif ($checkEmailStmt->rowCount() > 0) {
                $message = 'Email đã được sử dụng!';
                $messageType = 'error';
            } else {
                // Cập nhật thông tin người dùng
                $updateStmt = $pdo->prepare("UPDATE users SET 
                    username = :username, 
                    fullname = :fullname, 
                    phone = :phone, 
                    email = :email, 
                    password = :password, 
                    role = :role 
                    WHERE id = :id");
                
                $updateStmt->bindParam(':username', $username);
                $updateStmt->bindParam(':fullname', $fullname);
                $updateStmt->bindParam(':phone', $phone);
                $updateStmt->bindParam(':email', $email);
                $updateStmt->bindParam(':password', $password);
                $updateStmt->bindParam(':role', $role);
                $updateStmt->bindParam(':id', $userId);
                
                if ($updateStmt->execute()) {
                    $message = 'Cập nhật thông tin người dùng thành công!';
                    $messageType = 'success';
                    
                    // Cập nhật thông tin người dùng hiển thị
                    $user['username'] = $username;
                    $user['fullname'] = $fullname;
                    $user['phone'] = $phone;
                    $user['email'] = $email;
                    $user['role'] = $role;
                } else {
                    $message = 'Có lỗi xảy ra khi cập nhật thông tin người dùng!';
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
    
    .password-note {
        font-size: 12px;
        color: #7f8c8d;
        margin-top: 5px;
    }
';

// Bắt đầu nội dung trang
ob_start();
?>

<div class="content-wrapper">
    <div class="header">
        <h1>Chỉnh Sửa Người Dùng</h1>
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <div class="form-group">
            <label for="username">Tên đăng nhập <span style="color: red;">*</span></label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="fullname">Họ và tên <span style="color: red;">*</span></label>
            <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Số điện thoại <span style="color: red;">*</span></label>
            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required pattern="[0-9]{10}" title="Vui lòng nhập số điện thoại 10 chữ số">
        </div>
        
        <div class="form-group">
            <label for="email">Email <span style="color: red;">*</span></label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password">
            <p class="password-note">Để trống nếu không muốn thay đổi mật khẩu</p>
        </div>
        
        <div class="checkbox-group">
            <input type="checkbox" id="role" name="role" <?= $user['role'] == 1 ? 'checked' : '' ?>>
            <label for="role">Quản trị viên</label>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu Thay Đổi</button>
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