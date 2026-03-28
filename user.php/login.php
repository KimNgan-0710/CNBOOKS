<?php
session_start();
require 'connect.php'; // File kết nối database
include 'firebase-config.php';

// Khởi tạo Firebase Admin SDK
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount($serviceAccount);

$auth = $factory->createAuth();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra reCAPTCHA
    $recaptchaSecret = '6LfFUzArAAAAAKkShr-kztbjX0WdyJaLPjynOgl6';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha = file_get_contents($recaptchaUrl . '?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
    $recaptcha = json_decode($recaptcha);

    if (!$recaptcha || !$recaptcha->success) {
        echo "<script>alert('Vui lòng xác nhận reCAPTCHA!');</script>";
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        try {
            // Truy vấn kiểm tra tài khoản
            $stmt = $pdo->prepare("SELECT id, password, role, email, firebase_uid FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                // Thử xác thực với Firebase trước
                try {
                    $signInResult = $auth->signInWithEmailAndPassword($user['email'], $password);
                    // Nếu xác thực Firebase thành công
                    $firebaseUser = $auth->getUser($user['firebase_uid']);
                    if (!$firebaseUser->emailVerified) {
                        echo "<script>alert('Vui lòng xác thực email trước khi đăng nhập!');</script>";
                    } else {
                        // Cập nhật mật khẩu mới vào database
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $updateStmt->execute([$hashedPassword, $user['id']]);

                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = $user['role'];

                        // Kiểm tra xem có tham số redirect không
                        if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                            header("Location: " . $_GET['redirect']);
                        } else {
                            if ($user['role'] == 1) {
                                header("Location: index.php");
                            } else {
                                header("Location: index.php");
                            }
                        }
                        exit();
                    }
                } catch (\Kreait\Firebase\Exception\Auth\InvalidPassword $e) {
                    // Nếu xác thực Firebase thất bại, kiểm tra mật khẩu trong database (dành cho tài khoản cũ)
                    if (password_verify($password, $user['password'])) {
                        $firebaseUser = $auth->getUser($user['firebase_uid']);
                        if (!$firebaseUser->emailVerified) {
                            echo "<script>alert('Vui lòng xác thực email trước khi đăng nhập!');</script>";
                        } else {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $username;
                            $_SESSION['role'] = $user['role'];
                            if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                                header("Location: " . $_GET['redirect']);
                            } else {
                                if ($user['role'] == 1) {
                                    header("Location: index.php");
                                } else {
                                    header("Location: index.php");
                                }
                            }
                            exit();
                        }
                    } else {
                        echo "<script>alert('Tên đăng nhập hoặc mật khẩu không đúng!');</script>";
                    }
                }
            } else {
                echo "<script>alert('Tên đăng nhập hoặc mật khẩu không đúng!');</script>";
            }
        } catch (\Exception $e) {
            echo "<script>alert('Có lỗi xảy ra: " . $e->getMessage() . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Thêm Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.x.x/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.x.x/firebase-auth.js"></script>
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        // Khởi tạo Firebase
        const firebaseConfig = <?php echo json_encode($firebaseConfig); ?>;
        firebase.initializeApp(firebaseConfig);
    </script>
</head>
<body>
    <div class="container">
        <h2>Đăng nhập</h2>

        <!-- Kiểm tra nếu đã đăng nhập -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            <a href="logout.php">Đăng xuất</a>
        <?php else: ?>
            <form method="POST" id="loginForm">
                <div class="input-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" name="username" required>
                </div>
                <div class="input-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" required>
                </div>
                <div class="g-recaptcha" data-sitekey="6LfFUzArAAAAAK7X0As-cW-NW_L-A0rO-0cEpL6o"></div>
                <button type="submit">Đăng nhập</button>
            </form>
            <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
            <p><a href="forgot_password.php">Quên mật khẩu?</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
