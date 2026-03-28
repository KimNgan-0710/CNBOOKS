<?php
include 'connect.php';
include 'firebase-config.php';

// Khởi tạo Firebase Admin SDK
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount($serviceAccount);

$auth = $factory->createAuth();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role_id = isset($_POST['role_id']) ? 1 : 0;

    try {
        // Kiểm tra tên đăng nhập đã tồn tại chưa
        $checkStmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $checkStmt->execute([$username]);

        // Kiểm tra email đã tồn tại chưa
        $checkEmailStmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $checkEmailStmt->execute([$email]);

        if ($checkStmt->rowCount() > 0) {
            echo "<script>alert('Tên người dùng đã tồn tại!');</script>";
        } elseif ($checkEmailStmt->rowCount() > 0) {
            echo "<script>alert('Email đã được sử dụng!');</script>";
        } else {
            // Tạo user trong Firebase
            $userProperties = [
                'email' => $email,
                'emailVerified' => false,
                'password' => $password,
                'displayName' => $fullname,
                'disabled' => false,
            ];

            $createdUser = $auth->createUser($userProperties);

            // Gửi email xác thực
            $actionCodeSettings = [
                'url' => 'http://localhost/TT/EBooks/verify_email.php',
                'handleCodeInApp' => true,
            ];
            
            $auth->sendEmailVerificationLink($email, $actionCodeSettings);

            // Lưu thông tin vào database
            $stmt = $pdo->prepare("INSERT INTO users (username, fullname, phone, email, password, role, firebase_uid) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$username, $fullname, $phone, $email, password_hash($password, PASSWORD_DEFAULT), $role_id, $createdUser->uid])) {
                echo "<script>alert('Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.'); window.location='login.php';</script>";
            } else {
                // Nếu lưu vào database thất bại, xóa user khỏi Firebase
                $auth->deleteUser($createdUser->uid);
                echo "<script>alert('Đăng ký thất bại. Vui lòng thử lại!');</script>";
            }
        }
    } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
        echo "<script>alert('Email đã được sử dụng!');</script>";
    } catch (\Kreait\Firebase\Exception\Auth\InvalidPassword $e) {
        echo "<script>alert('Mật khẩu không hợp lệ!');</script>";
    } catch (\Exception $e) {
        echo "<script>alert('Có lỗi xảy ra: " . $e->getMessage() . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Thêm Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.x.x/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.x.x/firebase-auth.js"></script>
    <script>
        // Khởi tạo Firebase
        const firebaseConfig = <?php echo json_encode($firebaseConfig); ?>;
        firebase.initializeApp(firebaseConfig);
    </script>
</head>
<body>
<div class="container">
    <h2>Đăng ký tài khoản</h2>
    <form method="POST" id="registerForm">
        <div class="input-group">
            <label>Họ và tên</label>
            <input type="text" name="fullname" required>
        </div>
        <div class="input-group">
            <label>Số điện thoại</label>
            <input type="tel" name="phone" required pattern="[0-9]{10}" title="Vui lòng nhập số điện thoại 10 chữ số">
        </div>
        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="input-group">
            <label>Tên đăng nhập</label>
            <input type="text" name="username" required>
        </div>
        <div class="input-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" required minlength="6">
        </div>
        <button type="submit">Đăng ký</button>
    </form>
    <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
</div>
</body>
</html>
