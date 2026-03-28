<?php
require '../connect.php';

// Thiết lập tiêu đề trang
$page_title = 'Thêm Sản Phẩm Mới';

// Lấy danh sách danh mục
try {
    $categoryStmt = $pdo->query("SELECT * FROM type ORDER BY type_name");
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn danh mục: " . $e->getMessage());
}

// Xử lý form khi được gửi
$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $productName = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $typeName = $_POST['type_name'];
    $image = trim($_POST['image']);
    
    // Kiểm tra dữ liệu
    if (empty($productName) || empty($description) || $price <= 0 || $quantity <= 0 || empty($typeName) || empty($image)) {
        $message = 'Vui lòng điền đầy đủ thông tin và đảm bảo giá và số lượng lớn hơn 0!';
        $messageType = 'error';
    } else {
        try {
            // Kiểm tra xem sản phẩm đã tồn tại chưa
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE product_name = :name");
            $checkStmt->bindParam(':name', $productName);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                $message = 'Sản phẩm với tên này đã tồn tại!';
                $messageType = 'error';
            } else {
                // Lấy id lớn nhất hiện tại
                $stmt = $pdo->query("SELECT MAX(product_id) AS max_id FROM products");
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $new_id = $row['max_id'] + 1;

                // Thêm sản phẩm mới
                $stmt = $pdo->prepare("INSERT INTO products (product_id, product_name, discription, price, quantity, type_name, image) 
                                      VALUES (:id, :name, :discription, :price, :quantity, :type_name, :image)");
                
                $stmt->bindParam(':id', $new_id);
                $stmt->bindParam(':name', $productName);
                $stmt->bindParam(':discription', $description);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':quantity', $quantity);
                $stmt->bindParam(':type_name', $typeName);
                $stmt->bindParam(':image', $image);
                
                if ($stmt->execute()) {
                    $message = 'Thêm sản phẩm thành công!';
                    $messageType = 'success';
                    
                    // Reset form
                    $productName = $description = $image = '';
                    $price = $quantity = 0;
                } else {
                    $message = 'Có lỗi xảy ra khi thêm sản phẩm!';
                    $messageType = 'error';
                }
            }
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>

<?php



// Bắt đầu nội dung trang
ob_start();
?>

<div class="content-wrapper">
        <div class="header">
            <h1>Thêm Sản Phẩm Mới</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label for="product_name">Tên Sản Phẩm <span style="color: red;">*</span></label>
                <input type="text" id="product_name" name="product_name" value="<?= isset($productName) ? htmlspecialchars($productName) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Mô Tả <span style="color: red;">*</span></label>
                <textarea id="description" name="description" required><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Giá (VNĐ) <span style="color: red;">*</span></label>
                    <input type="number" id="price" name="price" min="0" step="1000" value="<?= isset($price) ? $price : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="quantity">Số Lượng <span style="color: red;">*</span></label>
                    <input type="number" id="quantity" name="quantity" min="1" value="<?= isset($quantity) ? $quantity : '' ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="type_name">Danh Mục <span style="color: red;">*</span></label>
                <select id="type_name" name="type_name" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['type_name']) ?>" <?= isset($typeName) && $typeName == $category['type_name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['type_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">URL Hình Ảnh <span style="color: red;">*</span></label>
                <input type="url" id="image" name="image" value="<?= isset($image) ? htmlspecialchars($image) : '' ?>" required onchange="previewImage(this.value)">
                <div class="preview-container">
                    <img id="imagePreview" class="image-preview" src="" alt="Xem trước hình ảnh">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu Sản Phẩm</button>
                <a href="products.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay Lại</a>
            </div>
        </form>
    </div>

<?php
// Kết thúc nội dung trang
$page_content = ob_get_clean();

// Nhúng layout
include 'admin_layout.php';
?>