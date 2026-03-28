<?php
require '../connect.php'; // Kết nối database

// Kiểm tra quyền admin đã được thực hiện trong admin_layout.php

// Lấy thống kê từ cơ sở dữ liệu
try {
    // Tổng số sản phẩm
    $productStmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $productStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Tổng số ưu đãi đang hoạt động
    $discountStmt = $pdo->query("SELECT COUNT(*) as total FROM discount_products WHERE NOW() BETWEEN start_date AND end_date");
    $activeDiscounts = $discountStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Tổng số danh mục
    $categoryStmt = $pdo->query("SELECT COUNT(*) as total FROM type");
    $totalCategories = $categoryStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Sản phẩm mới thêm gần đây (5 sản phẩm)
    $recentProductsStmt = $pdo->query("SELECT * FROM products ORDER BY product_id DESC LIMIT 5");
    $recentProducts = $recentProductsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ưu đãi sắp hết hạn (trong 7 ngày tới)
    $expiringDiscountsStmt = $pdo->query("SELECT d.*, p.product_name, p.image 
                                         FROM discount_products d 
                                         JOIN products p ON d.product_id = p.product_id 
                                         WHERE d.end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
                                         ORDER BY d.end_date ASC
                                         LIMIT 5");
    $expiringDiscounts = $expiringDiscountsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>

<div class="dashboard">
    <h1 class="dashboard-title">Bảng Điều Khiển</h1>
    <p class="welcome-message">Xin chào, Chào mừng bạn đến với trang quản trị.</p>
    
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-book"></i></div>
            <div class="stat-info">
                <h3><?= $totalProducts ?></h3>
                <p>Tổng Sản Phẩm</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-tags"></i></div>
            <div class="stat-info">
                <h3><?= $activeDiscounts ?></h3>
                <p>Ưu Đãi Đang Hoạt Động</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-list"></i></div>
            <div class="stat-info">
                <h3><?= $totalCategories ?></h3>
                <p>Danh Mục</p>
            </div>
        </div>
    </div>
    
    <div class="dashboard-sections">
        <div class="dashboard-section">
            <h2>Sản Phẩm Mới Thêm</h2>
            <div class="product-list">
                <?php if (empty($recentProducts)): ?>
                    <p class="no-data">Không có sản phẩm mới.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Hình Ảnh</th>
                                <th>Tên Sản Phẩm</th>
                                <th>Giá</th>
                                <th>Danh Mục</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentProducts as $product): ?>
                                <tr>
                                    <td><img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="product-thumbnail"></td>
                                    <td><?= htmlspecialchars($product['product_name']) ?></td>
                                    <td><?= number_format($product['price'], 0, ',', '.') ?> đ</td>
                                    <td><?= htmlspecialchars($product['type_name']) ?></td>
                                    <td>
                                        <a href="edit_product.php?id=<?= $product['product_id'] ?>" class="action-btn edit-btn">Sửa</a>
                                        <a href="view_product.php?id=<?= $product['product_id'] ?>" class="action-btn view-btn">Xem</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="dashboard-section">
            <h2>Ưu Đãi Sắp Hết Hạn</h2>
            <div class="discount-list">
                <?php if (empty($expiringDiscounts)): ?>
                    <p class="no-data">Không có ưu đãi sắp hết hạn.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Hình Ảnh</th>
                                <th>Tên Sản Phẩm</th>
                                <th>Giảm Giá</th>
                                <th>Ngày Kết Thúc</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expiringDiscounts as $discount): ?>
                                <tr>
                                    <td><img src="<?= htmlspecialchars($discount['image']) ?>" alt="<?= htmlspecialchars($discount['product_name']) ?>" class="product-thumbnail"></td>
                                    <td><?= htmlspecialchars($discount['product_name']) ?></td>
                                    <td><?= $discount['discount_percent'] ?>%</td>
                                    <td><?= date('d/m/Y H:i', strtotime($discount['end_date'])) ?></td>
                                    <td>
                                        <a href="fixdiscount.php?id=<?= $discount['product_id'] ?>" class="action-btn edit-btn">Sửa</a>
                                        <a href="deletediscount.php?id=<?= $discount['product_id'] ?>" class="action-btn delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa ưu đãi này?')">Xóa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="quick-actions">
        <h2>Thao Tác Nhanh</h2>
        <div class="action-buttons">
            <a href="add_product.php" class="quick-action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Thêm Sản Phẩm</span>
            </a>
            <a href="adddiscount.php" class="quick-action-btn">
                <i class="fas fa-tag"></i>
                <span>Thêm Ưu Đãi</span>
            </a>
            <a href="addpost.php" class="quick-action-btn">
                <i class="fas fa-edit"></i>
                <span>Thêm Bài Đăng</span>
            </a>
            <a href="managediscount.php" class="quick-action-btn">
                <i class="fas fa-list-alt"></i>
                <span>Quản Lý Ưu Đãi</span>
            </a>
        </div>
    </div>
</div>
</body>

</html>

<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
    }

    .dashboard {
        padding: 20px;
        background-color: #f5f7fa;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .dashboard-title {
        color: #2c3e50;
        font-size: 28px;
        margin-bottom: 10px;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }

    .welcome-message {
        color: #7f8c8d;
        margin-bottom: 30px;
        font-size: 16px;
    }

    .stats-container {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        flex: 1;
        min-width: 200px;
        display: flex;
        align-items: center;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        background: #3498db;
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 20px;
    }

    .stat-info h3 {
        font-size: 24px;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .stat-info p {
        color: #7f8c8d;
        font-size: 14px;
    }

    .dashboard-sections {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }

    .dashboard-section {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        flex: 1;
        min-width: 300px;
    }

    .dashboard-section h2 {
        color: #2c3e50;
        font-size: 20px;
        margin-bottom: 15px;
        border-bottom: 1px solid #ecf0f1;
        padding-bottom: 10px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th, .data-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ecf0f1;
    }

    .data-table th {
        background-color: #f8f9fa;
        color: #2c3e50;
        font-weight: 600;
    }

    .data-table tr:hover {
        background-color: #f8f9fa;
    }

    .product-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 5px;
    }

    .action-btn {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 12px;
        margin-right: 5px;
        text-align: center;
    }

    .edit-btn {
        background-color: #3498db;
        color: white;
        margin-bottom: 10px;
    }

    .view-btn {
        background-color: #2ecc71;
        color: white;
    }

    .delete-btn {
        background-color: #e74c3c;
        color: white;
    }

    .no-data {
        color: #7f8c8d;
        text-align: center;
        padding: 20px;
    }

    .quick-actions {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .quick-actions h2 {
        color: #2c3e50;
        font-size: 20px;
        margin-bottom: 15px;
        border-bottom: 1px solid #ecf0f1;
        padding-bottom: 10px;
    }

    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        border: 1px solid #ecf0f1;
        border-radius: 10px;
        padding: 15px;
        text-decoration: none;
        color: #2c3e50;
        transition: all 0.3s ease;
        flex: 1;
        min-width: 120px;
    }

    .quick-action-btn i {
        font-size: 24px;
        margin-bottom: 10px;
        color: #3498db;
    }

    .quick-action-btn:hover {
        background-color: #3498db;
        color: white;
        transform: translateY(-5px);
    }

    .quick-action-btn:hover i {
        color: white;
    }

    @media (max-width: 768px) {
        .stats-container, .dashboard-sections, .action-buttons {
            flex-direction: column;
        }
        
        .stat-card, .dashboard-section, .quick-action-btn {
            width: 100%;
        }
    }
</style>

<script>
    // Thêm Font Awesome nếu chưa có
    if (!document.querySelector('link[href*="font-awesome"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';
        document.head.appendChild(link);
    }
</script>