<?php
include 'header.php';
require 'connect.php';

// Kiểm tra xem có từ khóa tìm kiếm không
if (!isset($_GET['keyword']) || empty($_GET['keyword'])) {
    header('Location: product.php');
    exit;
}

$keyword = trim($_GET['keyword']);
$type_filter = isset($_GET['type']) ? (int)$_GET['type'] : 0;

// Xây dựng câu truy vấn tìm kiếm
$query = "SELECT p.*, t.type_name 
          FROM products p 
          JOIN type t ON p.type_name = t.type_name
          WHERE (p.product_name LIKE :keyword 
                OR p.description LIKE :keyword)";

$params = [':keyword' => "%$keyword%"];

// Thêm bộ lọc theo danh mục nếu có
if ($type_filter > 0) {
    $query .= " AND t.type_id = :type_id";
    $params[':type_id'] = $type_filter;
}

$query .= " ORDER BY p.product_id";

// Lấy danh sách các danh mục từ bảng type
try {
    $typeStmt = $pdo->prepare("SELECT * FROM type ORDER BY type_name");
    $typeStmt->execute();
    $types = $typeStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Lỗi khi lấy dữ liệu danh mục: " . $e->getMessage();
    $types = [];
}

// Thực hiện tìm kiếm
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Lỗi khi tìm kiếm: " . $e->getMessage();
    $searchResults = [];
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
?>

<div class="container" style="margin-top: 100px; padding: 20px;">
    <h1 style="color: #333; font-size: 28px; margin-bottom: 10px;">
        <i class="fas fa-search" style="color: #4a90e2;"></i> 
        Kết quả tìm kiếm cho: <span style="color: #4a90e2;">"<?php echo htmlspecialchars($keyword); ?>"</span>
    </h1>
    <p style="color: #666; margin-bottom: 20px;">
        <?php echo count($searchResults); ?> sản phẩm được tìm thấy
    </p>
    
    <!-- Bộ lọc danh mục -->
    <div class="filter-container" style="margin: 30px 0; background-color: #f8f9fa; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <h3 style="margin-bottom: 15px; color: #4a90e2; font-size: 18px; border-bottom: 2px solid #4a90e2; padding-bottom: 8px; display: inline-block;">
            <i class="fas fa-filter"></i> Lọc kết quả tìm kiếm
        </h3>
        <form action="search.php" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
            <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
            <div style="display: flex; align-items: center; gap: 10px;">
                <label for="type" style="font-weight: 500; color: #555;">Danh mục:</label>
                <select name="type" id="type" onchange="this.form.submit()" style="padding: 8px 15px; border: 1px solid #ddd; border-radius: 5px; background-color: white; min-width: 200px; cursor: pointer; outline: none; transition: border-color 0.3s;">
                    <option value="0">Tất cả danh mục</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?php echo $type['type_id']; ?>" <?php echo ($type_filter == $type['type_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['type_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" style="background: linear-gradient(to right, #6ec1e4, #4a90e2); color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; transition: background 0.3s;">
                <i class="fas fa-search"></i> Áp dụng
            </button>
        </form>
    </div>
    
    <?php if (empty($searchResults)): ?>
        <div class="no-results" style="text-align: center; margin: 50px 0; padding: 40px; background-color: #f8f9fa; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
            <h3 style="color: #555; margin-bottom: 15px; font-size: 24px;">Không tìm thấy kết quả nào</h3>
            <p style="color: #777; margin-bottom: 25px; font-size: 16px;">Không tìm thấy sản phẩm nào phù hợp với từ khóa "<?php echo htmlspecialchars($keyword); ?>"</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="javascript:history.back()" class="btn" style="display: inline-block; padding: 10px 20px; background-color: #f0f0f0; color: #555; text-decoration: none; border-radius: 5px; transition: all 0.3s;">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <a href="product.php" class="btn" style="display: inline-block; padding: 10px 20px; background: linear-gradient(to right, #6ec1e4, #4a90e2); color: white; text-decoration: none; border-radius: 5px; transition: all 0.3s;">
                    <i class="fas fa-shopping-bag"></i> Xem tất cả sản phẩm
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="search-results" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px; margin-top: 20px;">
            <?php foreach ($searchResults as $product): ?>
                <div class="product-card" style="border: 1px solid #eee; border-radius: 10px; overflow: hidden; transition: all 0.3s; box-shadow: 0 3px 10px rgba(0,0,0,0.08); position: relative;">
                    <?php if (isset($discountMap[$product['product_id']])): ?>
                        <div style="position: absolute; top: 10px; right: 10px; background: #e74c3c; color: white; padding: 5px 10px; border-radius: 20px; font-weight: bold; font-size: 12px; z-index: 2; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            -<?php echo $discountMap[$product['product_id']]['discount_percent']; ?>%
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-image" style="height: 200px; overflow: hidden; position: relative;">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.03);"></div>
                    </div>
                    
                    <div class="product-info" style="padding: 20px; background: white;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                            <h3 style="margin: 0; font-size: 16px; line-height: 1.4; max-height: 44px; overflow: hidden; flex: 1;"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        </div>
                        
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
                            <span style="font-size: 12px; color: #666; background: #f5f5f5; padding: 3px 8px; border-radius: 4px;">
                                <i class="fas fa-layer-group" style="margin-right: 4px;"></i> <?php echo htmlspecialchars($product['type_name']); ?>
                            </span>
                            <span style="font-size: 12px; color: #666; background: #f5f5f5; padding: 3px 8px; border-radius: 4px;">
                                <i class="fas fa-cubes" style="margin-right: 4px;"></i> SL: <?php echo htmlspecialchars($product['quantity']); ?>
                            </span>
                        </div>
                        
                        <?php if (isset($discountMap[$product['product_id']])): ?>
                            <?php $discount = $discountMap[$product['product_id']]; ?>
                            <div class="price" style="margin: 15px 0; font-weight: bold;">
                                <span style="text-decoration: line-through; color: #999; font-size: 14px; display: block; margin-bottom: 3px;"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                                <span style="color: #e74c3c; font-size: 20px;"><?php echo number_format($product['price'] * (1 - $discount['discount_percent']/100), 0, ',', '.'); ?>đ</span>
                            </div>
                        <?php else: ?>
                            <div class="price" style="margin: 15px 0; font-weight: bold; color: #e74c3c; font-size: 20px;">
                                <?php echo number_format($product['price'], 0, ',', '.'); ?>đ
                            </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; gap: 8px; margin-top: 15px;">
                            <a href="book.php?id=<?php echo $product['product_id']; ?>" class="view-btn" style="flex: 1; text-align: center; padding: 10px; background: linear-gradient(to right, #6ec1e4, #4a90e2); color: white; text-decoration: none; border-radius: 5px; font-size: 14px; transition: all 0.3s; box-shadow: 0 2px 5px rgba(74, 144, 226, 0.3);">
                                <i class="fas fa-eye"></i> Chi tiết
                            </a>
                            <button class="add-to-cart-btn" 
                                data-id="<?php echo $product['product_id']; ?>" 
                                data-name="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                data-price="<?php echo $product['price']; ?>" 
                                data-image="<?php echo htmlspecialchars($product['image']); ?>" 
                                style="flex: 1; padding: 10px; background: linear-gradient(to right, #2ecc71, #27ae60); color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; transition: all 0.3s; box-shadow: 0 2px 5px rgba(46, 204, 113, 0.3);">
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý thêm vào giỏ hàng
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const productName = this.getAttribute('data-name');
            const productPrice = this.getAttribute('data-price');
            const productImage = this.getAttribute('data-image');
            
            // Gửi yêu cầu AJAX để thêm sản phẩm vào giỏ hàng
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + encodeURIComponent(productId) +
                      '&product_name=' + encodeURIComponent(productName) +
                      '&product_price=' + encodeURIComponent(productPrice) +
                      '&product_image=' + encodeURIComponent(productImage) +
                      '&quantity=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đã thêm sản phẩm vào giỏ hàng!');
                    // Cập nhật số lượng sản phẩm trong giỏ hàng
                    document.getElementById('cart-count').textContent = data.cartCount;
                } else {
                    alert('Có lỗi xảy ra: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng.');
            });
        });
    });
});
</script>

<?php include 'footer.php'; ?>