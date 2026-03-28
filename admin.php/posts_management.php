<?php
require '../connect.php';

// Thiết lập tiêu đề trang
$page_title = 'Quản Lý Bài Đăng';

// Xử lý xóa bài đăng
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $postId = (int)$_GET['delete'];
    
    try {
        // Kiểm tra xem bài đăng có tồn tại không
        $checkStmt = $pdo->prepare("SELECT * FROM posts WHERE post_id = :id");
        $checkStmt->bindParam(':id', $postId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            // Xóa bài đăng
            $deleteStmt = $pdo->prepare("DELETE FROM posts WHERE post_id = :id");
            $deleteStmt->bindParam(':id', $postId);
            $deleteStmt->execute();
            
            echo "<script>alert('Xóa bài đăng thành công!'); window.location.href='posts_management.php';</script>";
        } else {
            echo "<script>alert('Bài đăng không tồn tại!'); window.location.href='posts_management.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Lỗi khi xóa bài đăng: " . $e->getMessage() . "');</script>";
    }
}

// Lấy danh sách bài đăng
try {
    $query = "SELECT * FROM posts ORDER BY post_id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// CSS bổ sung cho trang này
$extra_css = '
    .content-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }
    
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    
    .header h1 {
        color: #2c3e50;
        font-size: 24px;
    }
    
    .add-btn {
        background-color: #2ecc71;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: background-color 0.3s;
    }
    
    .add-btn:hover {
        background-color: #27ae60;
    }
    
    .posts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .post-card {
        background-color: #f8f9fa;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .post-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .post-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    
    .post-content {
        padding: 15px;
    }
    
    .post-id {
        font-size: 14px;
        color: #7f8c8d;
        margin-bottom: 5px;
    }
    
    .post-username {
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 10px;
    }
    
    .post-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
    }
    
    .action-btn {
        padding: 8px 12px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 14px;
        text-align: center;
        flex: 1;
        margin: 0 5px;
    }
    
    .edit-btn {
        background-color: #3498db;
        color: white;
    }
    
    .edit-btn:hover {
        background-color: #2980b9;
    }
    
    .delete-btn {
        background-color: #e74c3c;
        color: white;
    }
    
    .delete-btn:hover {
        background-color: #c0392b;
    }
    
    .no-posts {
        text-align: center;
        padding: 50px 0;
        color: #7f8c8d;
    }
    
    .no-posts i {
        font-size: 48px;
        margin-bottom: 15px;
        color: #bdc3c7;
    }
    
    .back-btn {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 15px;
        background-color: #7f8c8d;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s;
    }
    
    .back-btn:hover {
        background-color: #6c7a89;
    }
    
    @media (max-width: 768px) {
        .posts-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
    }
    
    @media (max-width: 480px) {
        .posts-grid {
            grid-template-columns: 1fr;
        }
    }
';

// Bắt đầu nội dung trang
ob_start();
?>

<div class="content-wrapper">
    <div class="header">
        <h1>Quản Lý Bài Đăng</h1>
        <a href="add_post.php" class="add-btn"><i class="fas fa-plus"></i> Thêm Bài Đăng</a>
    </div>
    
    <?php if (empty($posts)): ?>
        <div class="no-posts">
            <i class="fas fa-images"></i>
            <p>Không có bài đăng nào.</p>
        </div>
    <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <img src="<?= isset($post['image']) ? htmlspecialchars($post['image']) : '../images/no-image.jpg' ?>" alt="Bài đăng" class="post-image">
                    <div class="post-content">
                        <div class="post-id">ID: <?= $post['post_id'] ?></div>
                        <div class="post-username">Người đăng: <?= isset($post['user_name']) ? htmlspecialchars($post['user_name']) : 'Không xác định' ?></div>
                        <div class="post-actions">
                            <a href="edit_post.php?id=<?= $post['post_id'] ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i> Sửa</a>
                            <a href="posts_management.php?delete=<?= $post['post_id'] ?>" class="action-btn delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa bài đăng này?')"><i class="fas fa-trash"></i> Xóa</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại Dashboard</a>
</div>

<?php
// Kết thúc nội dung trang
$page_content = ob_get_clean();

// Nhúng layout
include 'admin_layout.php';
?>