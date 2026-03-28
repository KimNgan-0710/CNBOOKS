<?php
include 'header.php';
require 'connect.php';

// Lấy danh sách các danh mục từ bảng type
try {
    $typeStmt = $pdo->prepare("SELECT * FROM type ORDER BY type_name");
    $typeStmt->execute();
    $types = $typeStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Lỗi khi lấy dữ liệu danh mục: " . $e->getMessage();
    $types = [];
}

// Kiểm tra xem có lọc theo danh mục không
$selectedTypeId = isset($_GET['type']) ? (int)$_GET['type'] : 0;
$showDiscountOnly = isset($_GET['discount']) && $_GET['discount'] == 1;

// Xây dựng câu truy vấn dựa trên bộ lọc
$query = "SELECT p.*, t.type_name 
          FROM products p 
          JOIN type t ON p.type_name = t.type_name";

$params = [];

if ($selectedTypeId > 0) {
    $query .= " WHERE t.type_id = :type_id";
    $params[':type_id'] = $selectedTypeId;
}

$query .= " ORDER BY p.product_id";

// Lấy danh sách sách từ cơ sở dữ liệu
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $allProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Lỗi khi lấy dữ liệu sách: " . $e->getMessage();
    $allProducts = [];
}

// Lấy thông tin ưu đãi nếu có
try {
    $discountStmt = $pdo->prepare("SELECT * FROM discount_products WHERE NOW() BETWEEN start_date AND end_date AND remaining > 0");
    $discountStmt->execute();
    $discounts = $discountStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tạo mảng với key là product_id để dễ dàng tìm kiếm
    $discountMap = [];
    foreach ($discounts as $discount) {
        $discountMap[$discount['product_id']] = $discount;
    }
} catch(PDOException $e) {
    echo "Lỗi khi lấy dữ liệu ưu đãi: " . $e->getMessage();
    $discountMap = [];
}

// Lọc sản phẩm theo ưu đãi nếu cần
$products = [];
foreach ($allProducts as $product) {
    $hasDiscount = isset($discountMap[$product['product_id']]);
    
    // Nếu chỉ hiển thị sản phẩm có ưu đãi và sản phẩm này không có ưu đãi, bỏ qua
    if ($showDiscountOnly && !$hasDiscount) {
        continue;
    }
    
    $products[] = $product;
}
?>

<div class="shop-container" style="margin-top: 100px; padding: 20px; display: flex;">
    <!-- Sidebar - Danh mục sách -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Danh Mục Sách</h3>
        </div>
        <div class="sidebar-content">
            <ul class="category-list">
                <li class="<?php echo ($selectedTypeId == 0 && !$showDiscountOnly) ? 'active' : ''; ?>">
                    <a href="product.php">Tất cả sách</a>
                </li>
                <li class="<?php echo $showDiscountOnly ? 'active' : ''; ?>">
                    <a href="product.php?discount=1">Sách đang giảm giá</a>
                </li>
                <li class="category-divider"></li>
                <?php foreach ($types as $type): ?>
                <li class="<?php echo $selectedTypeId == $type['type_id'] ? 'active' : ''; ?>">
                    <a href="product.php?type=<?php echo $type['type_id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <!-- Main Content - Danh sách sách -->
    <div class="main-content">
        <h1 class="page-title">
            <?php 
            if ($selectedTypeId > 0) {
                foreach ($types as $type) {
                    if ($type['type_id'] == $selectedTypeId) {
                        echo 'Sách ' . htmlspecialchars($type['type_name']);
                        break;
                    }
                }
            } elseif ($showDiscountOnly) {
                echo 'Sách Đang Giảm Giá';
            } else {
                echo 'Tất Cả Sách';
            }
            ?>
        </h1>
        
        <div class="product-grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <?php 
                        // Kiểm tra xem sản phẩm có ưu đãi không
                        $hasDiscount = isset($discountMap[$product['product_id']]);
                        $finalPrice = $hasDiscount ? $discountMap[$product['product_id']]['discounted_price'] : $product['price'];
                        $originalPrice = $product['price'];
                        
                        // Đường dẫn hình ảnh
                        $imagePath = !empty($product['image']) ? $product['image'] : 'https://via.placeholder.com/250x350?text=No+Image';
                    ?>
                    <div class="product-card">
                        <?php if ($hasDiscount): ?>
                            <div class="discount-badge">
                                -<?php echo $discountMap[$product['product_id']]['discount_percent']; ?>%
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-image">
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        </div>
                        <div class="product-info">
                            <!-- Tên sách -->
                            <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            
                            <!-- Thể loại -->
                            <p class="product-type"><?php echo htmlspecialchars($product['type_name'] ?? 'Không có thể loại'); ?></p>
                            
                            <!-- Mô tả -->
                            <p class="product-description"><?php echo htmlspecialchars(substr($product['description'] ?? 'Không có mô tả', 0, 150)); ?><?php echo strlen($product['description'] ?? '') > 150 ? '...' : ''; ?></p>
                            
                            <!-- Phần giá và nút thêm vào giỏ -->
                            <div class="product-footer">
                                <div class="price-container">
                                    <?php if ($hasDiscount): ?>
                                        <span class="final-price"><?php echo number_format($finalPrice, 0, ',', '.'); ?> ₫</span>
                                        <span class="original-price"><?php echo number_format($originalPrice, 0, ',', '.'); ?> ₫</span>
                                    <?php else: ?>
                                        <span class="final-price"><?php echo number_format($finalPrice, 0, ',', '.'); ?> ₫</span>
                                    <?php endif; ?>
                                </div>
                                <button class="add-to-cart-btn" onclick="checkLoginAndAddToCart('<?php echo $product['product_id']; ?>', '<?php echo addslashes($product['product_name']); ?>', <?php echo $finalPrice; ?>, '<?php echo $imagePath; ?>')">
                                    <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-products">
                    <h3>Không tìm thấy sách nào.</h3>
                    <p>Vui lòng thử tìm kiếm với danh mục khác hoặc xem tất cả sách.</p>
                    <a href="product.php" class="back-btn">Xem tất cả sách</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    /* Layout chung */
    .shop-container {
        max-width: 1400px;
        margin: 0 auto;
        display: flex;
        gap: 30px;
    }
    
    /* Sidebar styles */
    .sidebar {
        width: 250px;
        min-width: 250px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        height: fit-content;
        position: sticky;
        top: 120px;
    }
    
    .sidebar-header {
        background: linear-gradient(to right, #6ec1e4, #4a90e2);
        color: white;
        padding: 15px 20px;
    }
    
    .sidebar-header h3 {
        margin: 0;
        font-size: 18px;
    }
    
    .sidebar-content {
        padding: 15px 0;
    }
    
    .category-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .category-list li {
        padding: 0;
        transition: background 0.3s;
    }
    
    .category-list li a {
        display: block;
        padding: 12px 20px;
        color: #555;
        text-decoration: none;
        transition: all 0.3s;
        border-left: 3px solid transparent;
    }
    
    .category-list li:hover a {
        background-color: #f5f5f5;
        color: #4a90e2;
        border-left-color: #4a90e2;
    }
    
    .category-list li.active a {
        background-color: #e6f2ff;
        color: #4a90e2;
        border-left-color: #4a90e2;
        font-weight: 600;
    }
    
    .category-divider {
        height: 1px;
        background-color: #eee;
        margin: 10px 0;
    }
    
    /* Main content styles */
    .main-content {
        flex: 1;
        min-width: 0;
    }
    
    .page-title {
        color: #1e90ff;
        margin-bottom: 30px;
        font-size: 28px;
        font-weight: 600;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    /* Product grid styles */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 30px;
        margin-bottom: 50px;
    }
    
    .product-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        height: 470px; /* Tăng chiều cao cho tất cả các card */
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    .product-card button:hover {
        background: #0066cc;
    }
    
    .product-image {
        height: 250px;
        overflow: hidden;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .product-info {
        padding: 15px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    
    .product-name {
        margin: 0 0 10px;
        color: #333;
        height: 50px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        font-size: 16px;
    }
    
    .product-type {
        color: #666;
        margin: 0 0 10px;
        font-size: 14px;
    }
    
    .product-description {
        color: #777;
        margin: 0 0 15px;
        font-size: 13px;
        height: 60px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }
    
    .product-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
        gap: 15px; /* Thêm khoảng cách giữa giá và nút */
    }
    
    .price-container {
        display: flex;
        flex-direction: column;
        min-width: 100px; /* Đảm bảo có đủ không gian cho giá */
    }
    
    .final-price {
        font-weight: bold;
        color: #1e90ff;
        font-size: 16px;
        white-space: nowrap;
        display: block; /* Hiển thị mỗi giá trên một dòng */
    }
    
    .original-price {
        text-decoration: line-through;
        color: #999;
        font-size: 13px;
        white-space: nowrap;
        display: block; /* Hiển thị mỗi giá trên một dòng */
    }
    
    .add-to-cart-btn {
        background: #1e90ff;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s ease;
        white-space: nowrap;
        font-size: 14px;
        min-width: 120px;
        text-align: center;
    }
    
    .add-to-cart-btn:hover {
        background: #0066cc;
    }
    
    .discount-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #ff4757;
        color: white;
        padding: 5px 10px;
        border-radius: 3px;
        font-weight: bold;
        z-index: 1;
    }
    
    /* No products message */
    .no-products {
        grid-column: 1 / -1;
        text-align: center;
        padding: 50px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .no-products h3 {
        color: #666;
        margin-bottom: 15px;
    }
    
    .no-products p {
        color: #999;
        margin-bottom: 20px;
    }
    
    .back-btn {
        display: inline-block;
        background: #4a90e2;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        transition: background 0.3s;
    }
    
    .back-btn:hover {
        background: #3a7bc8;
    }
    
    /* Responsive styles */
    @media (max-width: 992px) {
        .shop-container {
            flex-direction: column;
        }
        
        .sidebar {
            width: 100%;
            position: static;
            margin-bottom: 30px;
        }
        
        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        }
    }
    
    @media (max-width: 576px) {
        .product-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    // Hàm kiểm tra đăng nhập trước khi thêm vào giỏ hàng
    function checkLoginAndAddToCart(productId, productName, productPrice, productImage) {
        // Thêm sản phẩm vào giỏ hàng localStorage trước
        addToCart(productId, productName, productPrice, productImage);
        
        // Kiểm tra xem người dùng đã đăng nhập chưa
        fetch('check_login.php')
            .then(response => response.json())
            .then(data => {
                if (!data.logged_in) {
                    // Nếu chưa đăng nhập, hiển thị thông báo
                    const loginConfirm = confirm('Bạn chưa đăng nhập. Đăng nhập ngay để lưu giỏ hàng?');
                    if (loginConfirm) {
                        window.location.href = 'login.php?redirect=product.php';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
</script>

<?php
include 'footer.php';
?>