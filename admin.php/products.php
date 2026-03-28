<?php
require '../connect.php';

// Thiết lập tiêu đề trang
$page_title = 'Quản Lý Sản Phẩm';

// Nội dung trang sẽ được nhúng vào layout

// Xử lý tìm kiếm và lọc
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'product_id';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Xây dựng câu truy vấn
$query = "SELECT p.*, t.type_name 
          FROM products p 
          JOIN type t ON p.type_name = t.type_name
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (p.product_name LIKE :search OR p.description LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND t.type_id = :category";
    $params[':category'] = $category;
}

$query .= " ORDER BY $sort $order";

// Lấy danh sách sản phẩm
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Lấy danh sách danh mục
try {
    $categoryStmt = $pdo->query("SELECT * FROM type ORDER BY type_name");
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn danh mục: " . $e->getMessage());
}

// Xử lý xóa sản phẩm nếu có
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $productId = (int)$_GET['delete'];
    
    try {
        // Kiểm tra xem sản phẩm có tồn tại không
        $checkStmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :id");
        $checkStmt->bindParam(':id', $productId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            // Xóa sản phẩm
            $deleteStmt = $pdo->prepare("DELETE FROM products WHERE product_id = :id");
            $deleteStmt->bindParam(':id', $productId);
            $deleteStmt->execute();
            
            // Xóa các ưu đãi liên quan
            $deleteDiscountStmt = $pdo->prepare("DELETE FROM discount_products WHERE product_id = :id");
            $deleteDiscountStmt->bindParam(':id', $productId);
            $deleteDiscountStmt->execute();
            
            echo "<script>alert('Xóa sản phẩm thành công!'); window.location.href='products.php';</script>";
        } else {
            echo "<script>alert('Sản phẩm không tồn tại!'); window.location.href='products.php';</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Lỗi khi xóa sản phẩm: " . $e->getMessage() . "');</script>";
    }
}
?>

<?php
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
    
    .filters {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 200px;
    }
    
    .filter-group label {
        margin-bottom: 5px;
        color: #2c3e50;
        font-weight: 500;
    }
    
    .filter-group input, .filter-group select {
        padding: 8px 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }
    
    .filter-btn {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
        align-self: flex-end;
    }
    
    .filter-btn:hover {
        background-color: #2980b9;
    }
    
    .reset-btn {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
        text-decoration: none;
        display: inline-block;
        align-self: flex-end;
    }
    
    .reset-btn:hover {
        background-color: #c0392b;
    }
    
    .product-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .product-table th, .product-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ecf0f1;
    }
    
    .product-table th {
        background-color: #f8f9fa;
        color: #2c3e50;
        font-weight: 600;
    }
    
    .product-table tr:hover {
        background-color: #f8f9fa;
    }
    
    .product-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
    }
    
    .product-name {
        font-weight: 500;
        color: #2c3e50;
    }
    
    .product-description {
        color: #7f8c8d;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .product-price {
        font-weight: 500;
        color: #e74c3c;
    }
    
    .product-category {
        background-color: #3498db;
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        display: inline-block;
    }

    .list-actionbtn a {
        margin-bottom: 10px;
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
    }
    
    .view-btn {
        background-color: #2ecc71;
        color: white;
    }
    
    .delete-btn {
        background-color: #e74c3c;
        color: white;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
    
    .pagination a {
        display: inline-block;
        padding: 8px 12px;
        margin: 0 5px;
        border-radius: 5px;
        background-color: #f8f9fa;
        color: #2c3e50;
        text-decoration: none;
        transition: background-color 0.3s;
    }
    
    .pagination a.active {
        background-color: #3498db;
        color: white;
    }
    
    .pagination a:hover {
        background-color: #e9ecef;
    }
    
    .no-products {
        text-align: center;
        padding: 30px;
        color: #7f8c8d;
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
        .filters {
            flex-direction: column;
        }
        
        .product-table {
            display: block;
            overflow-x: auto;
        }
    }
';

// Bắt đầu nội dung trang
ob_start();
?>

<div class="content-wrapper">
        <div class="header">
            <h1>Quản Lý Sản Phẩm</h1>
            <a href="add_product.php" class="add-btn"><i class="fas fa-plus"></i> Thêm Sản Phẩm</a>
        </div>
        
        <form action="" method="GET" class="filters">
            <div class="filter-group">
                <label for="search">Tìm kiếm:</label>
                <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tên sản phẩm...">
            </div>
            
            <div class="filter-group">
                <label for="category">Danh mục:</label>
                <select id="category" name="category">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['type_id'] ?>" <?= $category == $cat['type_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['type_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="sort">Sắp xếp theo:</label>
                <select id="sort" name="sort">
                    <option value="product_id" <?= $sort == 'product_id' ? 'selected' : '' ?>>ID</option>
                    <option value="product_name" <?= $sort == 'product_name' ? 'selected' : '' ?>>Tên sản phẩm</option>
                    <option value="price" <?= $sort == 'price' ? 'selected' : '' ?>>Giá</option>
                    <option value="type_name" <?= $sort == 'type_name' ? 'selected' : '' ?>>Danh mục</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="order">Thứ tự:</label>
                <select id="order" name="order">
                    <option value="ASC" <?= $order == 'ASC' ? 'selected' : '' ?>>Tăng dần</option>
                    <option value="DESC" <?= $order == 'DESC' ? 'selected' : '' ?>>Giảm dần</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>&nbsp;</label>
                <div>
                    <button type="submit" class="filter-btn">Lọc</button>
                    <a href="products.php" class="reset-btn">Đặt lại</a>
                </div>
            </div>
        </form>
        
        <?php if (empty($products)): ?>
            <div class="no-products">
                <i class="fas fa-box-open" style="font-size: 48px; color: #bdc3c7; margin-bottom: 15px;"></i>
                <p>Không tìm thấy sản phẩm nào.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Mô tả</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Danh mục</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product['product_id'] ?></td>
                                <td><img src="<?= isset($product['image']) ? htmlspecialchars($product['image']) : '../images/no-image.jpg' ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="product-thumbnail"></td>
                                <td class="product-name"><?= htmlspecialchars($product['product_name']) ?></td>
                                <td class="product-description"><?= isset($product['description']) ? htmlspecialchars(substr($product['description'], 0, 100)) . (strlen($product['description']) > 100 ? '...' : '') : 'Không có mô tả' ?></td>
                                <td class="product-price"><?= number_format($product['price'], 0, ',', '.') ?> đ</td>
                                <td><?= $product['quantity'] ?></td>
                                <td><span class="product-category"><?= htmlspecialchars($product['type_name']) ?></span></td>
                                <td class="list-actionbtn">
                                    <a href="edit_product.php?id=<?= $product['product_id'] ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i> Sửa</a>
                                    <a href="view_product.php?id=<?= $product['product_id'] ?>" class="action-btn view-btn"><i class="fas fa-eye"></i> Xem</a>
                                    <a href="products.php?delete=<?= $product['product_id'] ?>" class="action-btn delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')"><i class="fas fa-trash"></i> Xóa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Phân trang (có thể thêm sau) -->
            <!-- <div class="pagination">
                <a href="#">&laquo;</a>
                <a href="#" class="active">1</a>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#">&raquo;</a>
            </div> -->
        <?php endif; ?>
        
        <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại Dashboard</a>
    </div>

<?php
// Kết thúc nội dung trang
$page_content = ob_get_clean();

// Nhúng layout
include 'admin_layout.php';
?>