<?php
require '../connect.php';

// Kiểm tra ID người dùng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID người dùng không hợp lệ!'); window.location.href='analytics_users.php';</script>";
    exit;
}

$userId = (int)$_GET['id'];

try {
    // Kiểm tra người dùng tồn tại
    $checkStmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
    $checkStmt->bindParam(':id', $userId);
    $checkStmt->execute();
    $user = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<script>alert('Không tìm thấy người dùng!'); window.location.href='analytics_users.php';</script>";
        exit;
    }
    
    // Xóa người dùng
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $deleteStmt->bindParam(':id', $userId);
    
    if ($deleteStmt->execute()) {
        echo "<script>alert('Xóa người dùng " . htmlspecialchars($user['username']) . " thành công!'); window.location.href='analytics_users.php';</script>";
    } else {
        echo "<script>alert('Có lỗi xảy ra khi xóa người dùng!'); window.location.href='analytics_users.php';</script>";
    }
} catch (PDOException $e) {
    echo "<script>alert('Lỗi: " . $e->getMessage() . "'); window.location.href='analytics_users.php';</script>";
}
?>