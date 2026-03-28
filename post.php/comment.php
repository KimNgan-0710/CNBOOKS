<?php
session_start();
require 'connect.php';

// Đảm bảo trả về JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ POST hoặc từ raw input
    $post_data = $_POST;
    
    // Nếu không có dữ liệu trong $_POST, thử đọc từ php://input
    if (empty($_POST)) {
        $json = file_get_contents('php://input');
        $post_data = json_decode($json, true);
    }
    
    // Kiểm tra dữ liệu
    if (isset($post_data['post_id'], $post_data['comment'])) {
        $post_id = $post_data['post_id'];
        $comment = trim($post_data['comment']);
        
        // Kiểm tra xem người dùng đã đăng nhập chưa
        if (isset($_SESSION['user_id'])) {
            // Lấy thông tin người dùng từ database
            $stmt = $pdo->prepare("SELECT username, fullname FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Ưu tiên sử dụng fullname, nếu không có thì dùng username
                $username = !empty($user['fullname']) ? $user['fullname'] : $user['username'];
            } else {
                // Nếu không tìm thấy user trong database, sử dụng tên từ input
                $username = isset($post_data['username']) && !empty($post_data['username']) 
                    ? trim($post_data['username']) 
                    : "Khách " . substr(md5(time() . rand()), 0, 5);
            }
        } else {
            // Người dùng chưa đăng nhập, lấy username từ input
            $username = isset($post_data['username']) && !empty($post_data['username']) 
                ? trim($post_data['username']) 
                : "Khách " . substr(md5(time() . rand()), 0, 5);
        }
        
        if (!empty($comment)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO comments (post_id, username, comment) VALUES (?, ?, ?)");
                if ($stmt->execute([$post_id, $username, $comment])) {
                    // Lấy ID của comment vừa thêm
                    $comment_id = $pdo->lastInsertId();
                    
                    echo json_encode([
                        "success" => true, 
                        "comment_id" => $comment_id,
                        "username" => $username, 
                        "comment" => $comment,
                        "post_id" => $post_id
                    ]);
                    exit;
                }
            } catch (PDOException $e) {
                echo json_encode([
                    "success" => false, 
                    "message" => "Lỗi database: " . $e->getMessage()
                ]);
                exit;
            }
        }
    }
    
    // Nếu có lỗi hoặc dữ liệu không hợp lệ
    echo json_encode([
        "success" => false, 
        "message" => "Dữ liệu không hợp lệ"
    ]);
}
?>


<!-- $postId = $_GET['post_id'];
$comments = $pdo->prepare("SELECT * FROM comments WHERE post_id = ?");
$comments->execute([$postId]);
$allComments = $comments->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($allComments);
?> -->

