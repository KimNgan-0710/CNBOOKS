<?php
require '../connect.php';

// Thiết lập tiêu đề trang
$page_title = 'Chỉnh Sửa Sản Phẩm';

// Kiểm tra ID sản phẩm
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID sản phẩm không hợp lệ!'); window.location.href='products.php';</script>";
    exit;
}

$productId = (int)$_GET['id'];

// Lấy thông tin sản phẩm
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :id");
    $stmt->bindParam(':id', $productId);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo "<script>alert('Không tìm thấy sản phẩm!'); window.location.href='products.php';</script>";
        exit;
    }
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
    if (empty($productName) || empty($description) || $price <= 0 || $quantity < 0 || empty($typeName) || empty($image)) {
        $message = 'Vui lòng điền đầy đủ thông tin và đảm bảo giá lớn hơn 0!';
        $messageType = 'error';
    } else {
        try {
            // Kiểm tra xem tên sản phẩm đã tồn tại chưa (nếu thay đổi tên)
            if ($productName != $product['product_name']) {
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE product_name = :name AND product_id != :id");
                $checkStmt->bindParam(':name', $productName);
                $checkStmt->bindParam(':id', $productId);
                $checkStmt->execute();
                
                if ($checkStmt->fetchColumn() > 0) {
                    $message = 'Sản phẩm với tên này đã tồn tại!';
                    $messageType = 'error';
                    // Không tiếp tục cập nhật
                    $product = [
                        'product_name' => $productName,
                        'description' => $description,
                        'price' => $price,
                        'quantity' => $quantity,
                        'type_name' => $typeName,
                        'image' => $image
                    ];
                    goto skip_update;
                }
            }
            
            // Cập nhật sản phẩm
            $stmt = $pdo->prepare("UPDATE products SET 
                                  product_name = :name, 
                                  description = :description, 
                                  price = :price, 
                                  quantity = :quantity, 
                                  type_name = :type_name, 
                                  image = :image 
                                  WHERE product_id = :id");
            
            $stmt->bindParam(':name', $productName);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':type_name', $typeName);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':id', $productId);
            
            if ($stmt->execute()) {
                $message = 'Cập nhật sản phẩm thành công!';
                $messageType = 'success';
                
                // Cập nhật thông tin sản phẩm hiển thị
                $product['product_name'] = $productName;
                $product['description'] = $description;
                $product['price'] = $price;
                $product['quantity'] = $quantity;
                $product['type_name'] = $typeName;
                $product['image'] = $image;
            } else {
                $message = 'Có lỗi xảy ra khi cập nhật sản phẩm!';
                $messageType = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
    
    skip_update:
}
?>

<?php
// CSS bổ sung cho trang này
$extra_css = '
    .content-wrapper {
        max-width: 800px;
        margin: 0 auto;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }
    
    .header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    
    .header h1 {
        color: #2c3e50;
        font-size: 24px;
    }
    
    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }
    
    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #2c3e50;
        font-weight: 500;
    }
    
    .form-group input, .form-group textarea, .form-group select {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        transition: border-color 0.3s;
    }
    
    .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
        border-color: #3498db;
        outline: none;
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .form-row {
        display: flex;
        gap: 20px;
    }
    
    .form-row .form-group {
        flex: 1;
    }
    
    .btn {
        display: inline-block;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        text-align: center;
        transition: background-color 0.3s;
        border: none;
    }
    
    .btn-primary {
        background-color: #3498db;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #2980b9;
    }
    
    .btn-secondary {
        background-color: #7f8c8d;
        color: white;
        text-decoration: none;
        margin-left: 10px;
    }
    
    .btn-secondary:hover {
        background-color: #6c7a89;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 30px;
    }
    
    .preview-container {
        margin-top: 20px;
        text-align: center;
    }
    
    .image-preview {
        max-width: 300px;
        max-height: 300px;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 5px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }
';

// JavaScript bổ sung cho trang này
$extra_js = '
    function previewImage(url) {
        const preview = document.getElementById("imagePreview");
        if (url) {
            preview.src = url;
            preview.style.display = "inline-block";
        } else {
            preview.style.display = "none";
        }
    }
';

// Bắt đầu nội dung trang
ob_start();
?>

<div class="content-wrapper">
        <div class="header">
            <h1>Chỉnh Sửa Sản Phẩm</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label for="product_name">Tên Sản Phẩm <span style="color: red;">*</span></label>
                <input type="text" id="product_name" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Mô Tả <span style="color: red;">*</span></label>
                <textarea id="description" name="description" required><?= isset($product['description']) ? htmlspecialchars($product['description']) : '' ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Giá (VNĐ) <span style="color: red;">*</span></label>
                    <input type="number" id="price" name="price" min="0" step="1000" value="<?= $product['price'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="quantity">Số Lượng <span style="color: red;">*</span></label>
                    <input type="number" id="quantity" name="quantity" min="0" value="<?= $product['quantity'] ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="type_name">Danh Mục <span style="color: red;">*</span></label>
                <select id="type_name" name="type_name" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['type_name']) ?>" <?= $product['type_name'] == $category['type_name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['type_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">URL Hình Ảnh <span style="color: red;">*</span></label>
                <input type="url" id="image" name="image" value="<?= isset($product['image']) ? htmlspecialchars($product['image']) : '' ?>" required onchange="previewImage(this.value)">
                <div class="preview-container">
                    <img id="imagePreview" class="image-preview" src="<?= isset($product['image']) ? htmlspecialchars($product['image']) : '../images/no-image.jpg' ?>" alt="Xem trước hình ảnh">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu Thay Đổi</button>
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