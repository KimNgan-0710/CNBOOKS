<?php
// Kết nối đến cơ sở dữ liệu
require '../connect.php';

// Lấy thông tin ưu đãi nếu có id
$discount = null;
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $product_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM discount_products WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $discount = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$discount) {
            echo "<script>alert('Không tìm thấy ưu đãi với ID đã cho!');</script>";
            echo "<script>window.location.href = 'managediscount.php';</script>";
            exit;
        }
        
        // Lấy thông tin sản phẩm
        $product_stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = :product_id");
        $product_stmt->bindParam(':product_id', $product_id);
        $product_stmt->execute();
        $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Đảm bảo đường dẫn hình ảnh đầy đủ
        if ($product && isset($product['image'])) {
            // Kiểm tra xem đường dẫn đã đầy đủ chưa
            if (strpos($product['image'], 'http') !== 0 && strpos($product['image'], '/') !== 0) {
                $product['image'] = '../post/image/' . $product['image'];
            }
        }
    } catch(PDOException $e) {
        echo "<script>alert('Lỗi khi lấy thông tin ưu đãi: " . $e->getMessage() . "');</script>";
    }
}

// Xử lý form khi được gửi đi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $product_id = $_POST['product_id'];
    $discount_percent = $_POST['discount_percent'];
    $discounted_price = $_POST['discounted_price'];
    $remaining = $_POST['remaining'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    try {
        // Lấy tên sản phẩm từ bảng products
        $stmt = $pdo->prepare("SELECT product_name FROM products WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            throw new PDOException("Không tìm thấy sản phẩm với ID: " . $product_id);
        }
        
        $product_name = $product['product_name'];
        
        // Chuẩn bị câu lệnh SQL để cập nhật dữ liệu
        $stmt = $pdo->prepare("UPDATE discount_products SET product_name = :product_name, discount_percent = :discount_percent, 
                              discounted_price = :discounted_price, remaining = :remaining, start_date = :start_date, end_date = :end_date 
                              WHERE product_id = :product_id");
        
        // Gán giá trị cho các tham số
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':discount_percent', $discount_percent);
        $stmt->bindParam(':discounted_price', $discounted_price);
        $stmt->bindParam(':remaining', $remaining);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        
        // Thực thi câu lệnh
        $result = $stmt->execute();
        
        if ($result) {
            // Hiển thị thông báo thành công
            echo "<script>alert('Cập nhật ưu đãi thành công!');</script>";
            
            // Chuyển hướng về trang quản lý ưu đãi sau khi cập nhật thành công
            echo "<script>window.location.href = 'managediscount.php';</script>";
        } else {
            $errorInfo = $stmt->errorInfo();
            echo "<script>alert('Không thể cập nhật ưu đãi! Lỗi: " . $errorInfo[2] . "');</script>";
        }
    } catch(PDOException $e) {
        // Hiển thị thông báo lỗi
        echo "<script>alert('Lỗi: " . $e->getMessage() . "');</script>";
        error_log("Lỗi khi cập nhật ưu đãi: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Ưu Đãi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: url('../post/image/br.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            display: flex;
            background: rgba(92, 168, 154, 0.9);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            max-width: 800px;
            width: 100%;
        }
        
        .promo-left, .promo-right {
            padding: 20px;
        }
        
        .promo-left {
            background: rgba(51, 51, 51, 0.8);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 40%;
        }
        
        .promo-right {
            width: 60%;
        }
        
        img {
            border: 2px solid #3498db;
            border-radius: 8px;
            margin-bottom: 10px;
            max-width: 100%;
            height: auto;
        }
        
        h2, h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #fff;
        }
        
        form {
            display: flex;
            flex-direction: column;
        }
        
        label {
            margin: 5px 0;
            font-weight: bold;
        }
        
        input, select, button {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: none;
            outline: none;
        }
        
        input[readonly] {
            background-color: #f0f0f0;
            color: #666;
        }
        
        button {
            background: #3498db;
            color: #fff;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #2980b9;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 10px;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="promo-left">
            <h3>Thông tin sản phẩm</h3>
            <img id="product-image" src="<?php echo isset($product) ? $product['image'] : 'default.jpg'; ?>" alt="Sản phẩm" width="200">
            <p id="product-name"><?php echo isset($discount) ? htmlspecialchars($discount['product_name']) : 'Chọn sản phẩm'; ?></p>
            <div id="original-price" style="margin-top: 10px; font-weight: bold;">
                <?php 
                if (isset($product)) {
                    echo 'Giá gốc: ' . number_format($product['price'], 0, ',', '.') . ' ₫';
                }
                ?>
            </div>
            <div id="remaining-info" style="margin-top: 5px; color: #2196F3;">
                <?php 
                if (isset($product) && isset($product['quantity'])) {
                    echo 'Số lượng còn lại: ' . $product['quantity'];
                }
                
                // Hiển thị thông tin số lượng được giảm giá
                if (isset($discount) && isset($discount['remaining'])) {
                    echo '<div style="margin-top: 5px;">Số lượng được giảm giá: ' . $discount['remaining'] . '</div>';
                }
                ?>
            </div>
        </div>
        <div class="promo-right">
            <h2>Sửa Ưu Đãi</h2>
            <form action="fixdiscount.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo isset($discount) ? htmlspecialchars($discount['product_id']) : ''; ?>">
                
                <label for="discount_percent">Giảm Giá (%):</label>
                <input type="number" name="discount_percent" id="discount_percent" value="<?php echo isset($discount) ? htmlspecialchars($discount['discount_percent']) : ''; ?>" required>
                
                <label for="discounted_price">Giá Sau Khi Giảm:</label>
                <input type="number" name="discounted_price" id="discounted_price" value="<?php echo isset($discount) ? htmlspecialchars($discount['discounted_price']) : ''; ?>" required>
                <div id="savings-info" style="color: #f44336; margin-top: 5px;">
                    <?php 
                    if (isset($discount) && isset($product)) {
                        $savingsAmount = $product['price'] - $discount['discounted_price'];
                        $savingsPercent = round(($savingsAmount / $product['price']) * 100, 1);
                        echo 'Tiết kiệm: ' . number_format($savingsAmount, 0, ',', '.') . ' ₫ (' . $savingsPercent . '%)';
                    }
                    ?>
                </div>
                
                <label for="remaining">Số Lượng Sách Được Giảm Giá:</label>
                <input type="number" name="remaining" id="remaining" value="<?php echo isset($discount) ? htmlspecialchars($discount['remaining']) : ''; ?>" required>
                
                <label for="start_date">Ngày Bắt Đầu:</label>
                <input type="datetime-local" name="start_date" id="start_date" value="<?php echo isset($discount) ? date('Y-m-d\TH:i', strtotime($discount['start_date'])) : ''; ?>" required>
                
                <label for="end_date">Ngày Kết Thúc:</label>
                <input type="datetime-local" name="end_date" id="end_date" value="<?php echo isset($discount) ? date('Y-m-d\TH:i', strtotime($discount['end_date'])) : ''; ?>" required>
                
                <button type="submit">Cập Nhật Ưu Đãi</button>
                <a href="managediscount.php" class="back-link">← Quay lại danh sách ưu đãi</a>
            </form>
        </div>
    </div>
    
    <script>
        // Cập nhật giá giảm khi thay đổi phần trăm giảm giá
        document.addEventListener('DOMContentLoaded', function() {
            // Lấy giá gốc từ API
            <?php if(isset($product)): ?>
            let originalPrice = <?php echo $product['price']; ?>;
            
            // Hàm tính giá sau khi giảm
            function calculateDiscountedPrice() {
                let discountPercent = parseFloat(document.getElementById('discount_percent').value);
                if (!isNaN(discountPercent) && discountPercent > 0) {
                    // Tính giá sau khi giảm
                    let discountAmount = originalPrice * discountPercent / 100;
                    let discountedPrice = originalPrice - discountAmount;
                    
                    // Làm tròn xuống đến hàng nghìn gần nhất để có giá đẹp hơn
                    discountedPrice = Math.floor(discountedPrice / 1000) * 1000;
                    
                    // Cập nhật giá trị vào trường input
                    document.getElementById('discounted_price').value = discountedPrice;
                    
                    // Hiển thị thông tin giảm giá
                    let savingsAmount = originalPrice - discountedPrice;
                    let savingsPercent = (savingsAmount / originalPrice * 100).toFixed(1);
                    
                    // Hiển thị thông tin tiết kiệm
                    let savingsInfo = document.getElementById('savings-info');
                    if (savingsInfo) {
                        savingsInfo.innerHTML = 'Tiết kiệm: ' + formatCurrency(savingsAmount) + ' (' + savingsPercent + '%)';
                    }
                }
            }
            
            // Hàm định dạng tiền tệ
            function formatCurrency(amount) {
                return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
            }
            
            // Sự kiện khi nhập phần trăm giảm giá
            document.getElementById('discount_percent').addEventListener('input', calculateDiscountedPrice);
            
            // Tính giá ban đầu nếu đã có phần trăm giảm giá
            if (document.getElementById('discount_percent').value) {
                calculateDiscountedPrice();
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>
