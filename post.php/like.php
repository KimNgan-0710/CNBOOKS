<?php
require 'connect.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    
    // Tạo một ID duy nhất cho người dùng (sử dụng session hoặc IP nếu không đăng nhập)
    $user_identifier = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_SERVER['REMOTE_ADDR'];
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'anonymous';
    
    // Kiểm tra xem người dùng đã like bài viết này chưa
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ? AND username = ? AND likes = 1 AND comment = ''");
    $checkStmt->execute([$post_id, $user_identifier]);
    $hasLiked = $checkStmt->fetchColumn() > 0;
    
    if ($hasLiked) {
        // Nếu đã like, xóa lượt like
        $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ? AND username = ? AND likes = 1 AND comment = ''");
        $stmt->execute([$post_id, $user_identifier]);
        $action = 'unliked';
    } else {
        // Nếu chưa like, thêm lượt like mới
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, likes, comment, username) VALUES (?, 1, '', ?)");
        $stmt->execute([$post_id, $user_identifier]);
        $action = 'liked';
    }
    
    // Đếm tổng số lượt thích cho bài viết này
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ? AND likes = 1");
    $stmt->execute([$post_id]);
    $likes = $stmt->fetchColumn();
    
    echo json_encode([
        "success" => true, 
        "likes" => $likes,
        "action" => $action
    ]);
}
?>
