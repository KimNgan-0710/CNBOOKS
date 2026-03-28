<?php
include 'firebase-config.php';

// Khởi tạo Firebase Admin SDK
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount($serviceAccount);

$auth = $factory->createAuth();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    try {
        // Gửi link đặt lại mật khẩu
        $actionCodeSettings = [
            'url' => 'http://localhost/TT/EBooks/login.php', // Sau khi đặt lại mật khẩu sẽ chuyển về trang đăng nhập
            'handleCodeInApp' => true,
        ];
        $resetLink = $auth->sendPasswordResetLink($email, $actionCodeSettings);

        echo "<script>alert('Đã gửi link đặt lại mật khẩu tới email của bạn. Vui lòng kiểm tra hộp thư!'); window.location='login.php';</script>";
    } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
        echo "<script>alert('Không tìm thấy tài khoản với email này!');</script>";
    } catch (\Exception $e) {
        echo "<script>alert('Có lỗi xảy ra: " . $e->getMessage() . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h2>Quên mật khẩu</h2>
    <form method="POST">
        <div class="input-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <button type="submit">Gửi link đặt lại mật khẩu</button>
    </form>
    <p>Quay lại <a href="login.php">Đăng nhập</a></p>
</div>
</body>
</html> 