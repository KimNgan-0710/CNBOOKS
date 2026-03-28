<?php
require '../connect.php';

// Thiết lập tiêu đề trang
$page_title = 'Chi Tiết Sản Phẩm';

// Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID sản phẩm không hợp lệ!'); window.location.href='products.php';</script>";
    exit;
}

$productId = (int)$_GET['id'];

// Lấy thông tin sản phẩm
try {
    $stmt = $pdo->prepare("SELECT p.*, t.type_id 
                          FROM products p 
                          JOIN type t ON p.type_name = t.type_name 
                          WHERE p.product_id = :id");
    $stmt->bindParam(':id', $productId);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo "<script>alert('Không tìm thấy sản phẩm!'); window.location.href='products.php';</script>";
        exit;
    }
    
    // Kiểm tra xem sản phẩm có ưu đãi không
    $discountStmt = $pdo->prepare("SELECT * FROM discount_products WHERE product_id = :id");
    $discountStmt->bindParam(':id', $productId);
    $discountStmt->execute();
    $discount = $discountStmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>

<?php
// CSS bổ sung cho trang này
$extra_css = '
    .content-wrapper {
        max-width: 1000px;
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
        margin-bottom: 30px;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    
    .header h1 {
        color: #2c3e50;
        font-size: 24px;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
    }
    
    .btn {
        display: inline-block;
        padding: 8px 15px;
        border-radius: 5px;
        font-size: 14px;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        transition: background-color 0.3s;
    }
    
    .btn-primary {
        background-color: #3498db;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #2980b9;
    }
    
    .btn-danger {
        background-color: #e74c3c;
        color: white;
    }
    
    .btn-danger:hover {
        background-color: #c0392b;
    }
    
    .btn-secondary {
        background-color: #7f8c8d;
        color: white;
    }
    
    .btn-secondary:hover {
        background-color: #6c7a89;
    }
    
    .product-details {
        display: flex;
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .product-image {
        flex: 0 0 300px;
    }
    
    .product-image img {
        width: 100%;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    
    .product-info {
        flex: 1;
    }
    
    .product-name {
        font-size: 24px;
        color: #2c3e50;
        margin-bottom: 10px;
    }
    
    .product-category {
        display: inline-block;
        background-color: #3498db;
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 14px;
        margin-bottom: 15px;
    }
    
    .product-price {
        font-size: 22px;
        color: #e74c3c;
        font-weight: bold;
        margin-bottom: 15px;
    }
    
    .product-discount {
        background-color: #2ecc71;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
        margin-left: 10px;
    }
    
    .product-quantity {
        font-size: 16px;
        color: #2c3e50;
        margin-bottom: 15px;
    }
    
    .product-description {
        color: #7f8c8d;
        line-height: 1.6;
        margin-bottom: 20px;
    }
    
    .section-title {
        font-size: 18px;
        color: #2c3e50;
        margin-bottom: 15px;
        border-bottom: 1px solid #ecf0f1;
        padding-bottom: 5px;
    }
    
    .discount-details {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    
    .discount-info {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .discount-item {
        flex: 1;
        min-width: 200px;
    }
    
    .discount-label {
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .discount-value {
        color: #7f8c8d;
    }
    
    .no-discount {
        color: #7f8c8d;
        font-style: italic;
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
        .product-details {
            flex-direction: column;
        }
        
        .product-image {
            flex: 0 0 auto;
            margin-bottom: 20px;
        }
    }
';

// Bắt đầu nội dung trang
ob_start();
?>

<div class="content-wrapper">
        <div class="header">
            <h1>Chi Tiết Sản Phẩm</h1>
            <div class="action-buttons">
                <a href="edit_product.php?id=<?= $product['product_id'] ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Sửa</a>
                <a href="products.php?delete=<?= $product['product_id'] ?>" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')"><i class="fas fa-trash"></i> Xóa</a>
            </div>
        </div>
        
        <div class="product-details">
            <div class="product-image">
                <img src="<?= isset($product['image']) ? htmlspecialchars($product['image']) : '../images/no-image.jpg' ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
            </div>
            
            <div class="product-info">
                <h2 class="product-name"><?= htmlspecialchars($product['product_name']) ?></h2>
                <span class="product-category"><?= htmlspecialchars($product['type_name']) ?></span>
                
                <div class="product-price">
                    <?= number_format($product['price'], 0, ',', '.') ?> đ
                    <?php if ($discount): ?>
                        <span class="product-discount">Giảm <?= $discount['discount_percent'] ?>%</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-quantity">
                    <i class="fas fa-cubes"></i> Số lượng: <?= $product['quantity'] ?>
                </div>
                
                <h3 class="section-title">Mô Tả Sản Phẩm</h3>
                <div class="product-description">
                    <?= isset($product['description']) ? nl2br(htmlspecialchars($product['description'])) : 'Không có mô tả cho sản phẩm này.' ?>
                </div>
            </div>
        </div>
        
        <h3 class="section-title">Thông Tin Ưu Đãi</h3>
        <?php if ($discount): ?>
            <div class="discount-details">
                <div class="discount-info">
                    <div class="discount-item">
                        <div class="discount-label">Phần trăm giảm giá:</div>
                        <div class="discount-value"><?= $discount['discount_percent'] ?>%</div>
                    </div>
                    
                    <div class="discount-item">
                        <div class="discount-label">Giá sau khi giảm:</div>
                        <div class="discount-value"><?= number_format($discount['discounted_price'], 0, ',', '.') ?> đ</div>
                    </div>
                    
                    <div class="discount-item">
                        <div class="discount-label">Ngày bắt đầu:</div>
                        <div class="discount-value"><?= date('d/m/Y H:i', strtotime($discount['start_date'])) ?></div>
                    </div>
                    
                    <div class="discount-item">
                        <div class="discount-label">Ngày kết thúc:</div>
                        <div class="discount-value"><?= date('d/m/Y H:i', strtotime($discount['end_date'])) ?></div>
                    </div>
                    
                    <div class="discount-item">
                        <div class="discount-label">Số lượng còn lại:</div>
                        <div class="discount-value"><?= $discount['remaining'] ?></div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p class="no-discount">Sản phẩm này chưa có ưu đãi. <a href="adddiscount.php?id=<?= $product['product_id'] ?>" class="btn btn-primary"><i class="fas fa-tag"></i> Thêm Ưu Đãi</a></p>
        <?php endif; ?>
        
        <a href="products.php" class="back-btn"><i class="fas fa-arrow-left"></i> Quay Lại Danh Sách Sản Phẩm</a>
    </div>

<?php
// Kết thúc nội dung trang
$page_content = ob_get_clean();

// Nhúng layout
include 'admin_layout.php';
?>