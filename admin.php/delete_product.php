<?php
require '../connect.php';

// Kiểm tra xem có ID sản phẩm được truyền vào không
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID sản phẩm không hợp lệ!'); window.location.href='products.php';</script>";
    exit;
}

$productId = (int)$_GET['id'];

try {
    // Kiểm tra xem sản phẩm có tồn tại không
    $checkStmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :id");
    $checkStmt->bindParam(':id', $productId);
    $checkStmt->execute();
    $product = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo "<script>alert('Không tìm thấy sản phẩm!'); window.location.href='products.php';</script>";
        exit;
    }
    
    // Kiểm tra xem sản phẩm có trong bảng discount_products không
    $discountStmt = $pdo->prepare("SELECT * FROM discount_products WHERE product_id = :id");
    $discountStmt->bindParam(':id', $productId);
    $discountStmt->execute();
    $discount = $discountStmt->fetch(PDO::FETCH_ASSOC);
    
    // Nếu sản phẩm có trong bảng discount_products, xóa nó trước
    if ($discount) {
        $deleteDiscountStmt = $pdo->prepare("DELETE FROM discount_products WHERE product_id = :id");
        $deleteDiscountStmt->bindParam(':id', $productId);
        $deleteDiscountStmt->execute();
    }
    
    // Xóa sản phẩm từ bảng products
    $deleteStmt = $pdo->prepare("DELETE FROM products WHERE product_id = :id");
    $deleteStmt->bindParam(':id', $productId);
    $result = $deleteStmt->execute();
    
    if ($result) {
        echo "<script>alert('Xóa sản phẩm thành công!'); window.location.href='products.php';</script>";
    } else {
        echo "<script>alert('Có lỗi xảy ra khi xóa sản phẩm!'); window.location.href='products.php';</script>";
    }
} catch (PDOException $e) {
    // Kiểm tra xem lỗi có phải do ràng buộc khóa ngoại không
    if ($e->getCode() == '23000') {
        echo "<script>
            alert('Không thể xóa sản phẩm này vì nó đang được sử dụng trong các đơn hàng hoặc dữ liệu khác!'); 
            window.location.href='products.php';
        </script>";
    } else {
        echo "<script>
            alert('Lỗi: " . addslashes($e->getMessage()) . "'); 
            window.location.href='products.php';
        </script>";
    }
}
?>