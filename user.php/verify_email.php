<?php
include 'firebase-config.php';

// Khởi tạo Firebase Admin SDK
require __DIR__ . '/vendor/autoload.php';
use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount($serviceAccount);

$auth = $factory->createAuth();

// Lấy mã xác thực từ URL
$oobCode = $_GET['oobCode'] ?? '';

if (!empty($oobCode)) {
    try {
        // Xác thực email
        $auth->verifyEmail($oobCode);
        echo "<script>alert('Email đã được xác thực thành công!'); window.location='login.php';</script>";
    } catch (\Exception $e) {
        echo "<script>alert('Có lỗi xảy ra khi xác thực email: " . $e->getMessage() . "'); window.location='login.php';</script>";
    }
} else {
    echo "<script>alert('Mã xác thực không hợp lệ!'); window.location='login.php';</script>";
}
?> 