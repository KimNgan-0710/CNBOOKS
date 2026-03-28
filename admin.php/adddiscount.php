<?php
// Xử lý form khi được gửi đi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require '../connect.php';
    
    // Debug: Hiển thị dữ liệu form
    error_log("Form data: " . print_r($_POST, true));
    
    // Lấy dữ liệu từ form
    $product_id = $_POST['product_id'];
    $discount_percent = $_POST['discount_percent'];
    $discounted_price = $_POST['discounted_price'];
    $remaining = $_POST['remaining']; // Số lượng sách được giảm giá
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    // Debug: Hiển thị dữ liệu đã xử lý
    error_log("Processed data: product_id=$product_id, discount_percent=$discount_percent, discounted_price=$discounted_price, remaining=$remaining, start_date=$start_date, end_date=$end_date");
    
    try {
        // Lấy tên sản phẩm từ bảng products trước
        $stmt = $pdo->prepare("SELECT product_name FROM products WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            throw new PDOException("Không tìm thấy sản phẩm với ID: " . $product_id);
        }
        
        $product_name = $product['product_name'];
        
        // Kiểm tra xem sản phẩm đã có trong bảng discount_products chưa dựa trên product_name và discount_percent
        $check_stmt = $pdo->prepare("SELECT * FROM discount_products WHERE product_name = :product_name AND discount_percent = :discount_percent");
        $check_stmt->bindParam(':product_name', $product_name);
        $check_stmt->bindParam(':discount_percent', $discount_percent);
        $check_stmt->execute();
        $existing_discount = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug: Hiển thị thông tin kiểm tra
        error_log("Kiểm tra trùng lặp: product_name=$product_name, discount_percent=$discount_percent, existing=" . ($existing_discount ? 'true' : 'false'));
        
        if ($existing_discount) {
            // Sản phẩm đã có ưu đãi với cùng phần trăm giảm giá
            $message = "Sản phẩm này đã có ưu đãi với phần trăm giảm giá này. Bạn có muốn cập nhật ưu đãi hiện có không?";
            echo "<script>
                if (confirm('" . $message . "')) {
                    window.location.href = 'fixdiscount.php?id=" . $product_id . "';
                } else {
                    window.location.href = 'managediscount.php';
                }
            </script>";
            exit; // Thêm exit để ngăn code tiếp tục thực thi
        } else {
            // Sản phẩm chưa có ưu đãi với phần trăm giảm giá này
            
            // Chuẩn bị câu lệnh SQL để chèn dữ liệu vào bảng discount_products
            $stmt = $pdo->prepare("INSERT INTO discount_products (product_id, product_name, discount_percent, discounted_price, start_date, end_date, remaining) 
                                  VALUES (:product_id, :product_name, :discount_percent, :discounted_price, :start_date, :end_date, :remaining)");
            
            // Gán giá trị cho các tham số
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':product_name', $product_name);
            $stmt->bindParam(':discount_percent', $discount_percent);
            $stmt->bindParam(':discounted_price', $discounted_price);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':remaining', $remaining); // Lưu số lượng sách được giảm giá vào trường remaining
            
            // Debug: Hiển thị thông tin trước khi thực thi
            error_log("Thông tin INSERT: product_id=$product_id, product_name=$product_name, discount_percent=$discount_percent, discounted_price=$discounted_price, start_date=$start_date, end_date=$end_date, remaining=$remaining");
            
            // Thực thi câu lệnh
            $result = $stmt->execute();
            
            if ($result) {
                // Kiểm tra xem dữ liệu đã được lưu thành công chưa
                $check_insert = $pdo->prepare("SELECT COUNT(*) FROM discount_products WHERE product_name = :product_name AND discount_percent = :discount_percent");
                $check_insert->bindParam(':product_name', $product_name);
                $check_insert->bindParam(':discount_percent', $discount_percent);
                $check_insert->execute();
                $inserted = $check_insert->fetchColumn();
                
                // Debug: Hiển thị kết quả kiểm tra
                error_log("Kiểm tra INSERT: product_name=$product_name, discount_percent=$discount_percent, inserted=$inserted");
                
                if ($inserted > 0) {
                    // Hiển thị thông báo thành công
                    echo "<script>alert('Thêm ưu đãi thành công!');</script>";
                    
                    // Chuyển hướng về trang quản lý ưu đãi sau khi thêm thành công
                    echo "<script>window.location.href = 'managediscount.php';</script>";
                } else {
                    echo "<script>alert('Không thể lưu ưu đãi. Vui lòng kiểm tra lại dữ liệu!');</script>";
                    // Debug: Hiển thị thông tin lỗi
                    error_log("Lỗi: Không tìm thấy dữ liệu sau khi INSERT với product_name=$product_name và discount_percent=$discount_percent");
                }
            } else {
                // Lấy thông tin lỗi từ PDO
                $errorInfo = $stmt->errorInfo();
                echo "<script>alert('Không thể thực thi câu lệnh SQL! Lỗi: " . $errorInfo[2] . "');</script>";
                // Debug: Hiển thị thông tin lỗi
                error_log("Lỗi SQL: " . implode(", ", $errorInfo));
            }
        }
    } catch(PDOException $e) {
        // Hiển thị thông báo lỗi chi tiết
        echo "<script>alert('Lỗi: " . $e->getMessage() . "\\n\\nSQL State: " . $e->getCode() . "');</script>";
        
        // Ghi log lỗi để dễ dàng debug
        error_log("Lỗi khi thêm ưu đãi: " . $e->getMessage() . " | SQL State: " . $e->getCode());
        
        // Hiển thị thông tin lỗi trên trang
        echo "<div style='background-color: #ffcccc; color: #cc0000; padding: 10px; margin: 10px 0; border: 1px solid #cc0000;'>
            <strong>Lỗi:</strong> " . htmlspecialchars($e->getMessage()) . "<br>
            <strong>SQL State:</strong> " . htmlspecialchars($e->getCode()) . "
        </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Ưu Đãi</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('../post/image/br.jpg') no-repeat center center fixed;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            display: flex;
            background:rgb(92, 168, 154);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(124, 223, 236, 0.5);
            max-width: 800px;
            width: 100%;
        }
        .promo-left, .promo-right {
            padding: 20px;
        }
        .promo-left {
            background: #333;
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
            border: 2px red;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        h2, h3 {
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin: 5px 0;
        }
        input, select, button {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: none;
            outline: none;
        }
        button {
            background:rgb(243, 22, 125);
            color: #fff;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background:rgb(219, 145, 179);
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="promo-left">
            <h3>Thông tin sản phẩm</h3>
            <img id="product-image" src="default.jpg" alt="Sản phẩm" width="200">
            <p>Chọn sản phẩm để xem thông tin.</p>
            <div id="original-price" style="margin-top: 10px; font-weight: bold; display: none;"></div>
            <div id="remaining-info" style="margin-top: 5px; color: #2196F3; display: none;"></div>
        </div>
        <div class="promo-right">
            <h2>Thêm Ưu Đãi</h2>
            <form action="adddiscount.php" method="POST">
                <label for="product">Chọn Sản Phẩm:</label>
                <select name="product_id" id="product-select" required>
                    <option value="">-- Chọn sản phẩm --</option>
                    <?php
                    require '../connect.php';
                    $query = $pdo->query("SELECT * FROM products");
                    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$row['product_id']}' data-price='{$row['price']}' data-image='{$row['image']}' data-remaining='{$row['quantity']}'>{$row['product_name']}</option>";
                    }
                    ?>
                </select>
                
                <label for="discount_percent">Giảm Giá (%):</label>
                <input type="number" name="discount_percent" id="discount_percent" required>
                
                <label for="discounted_price">Giá Sau Khi Giảm:</label>
                <input type="number" name="discounted_price" id="discounted_price" readonly>
                <div id="savings-info" style="color: #f44336; margin-top: 5px; display: none;"></div>
                

                <label for="discount_quantity">Số Lượng Sách Được Giảm Giá:</label>
                <input type="number" name="remaining" id="discount_quantity" required>
                
                <label for="start_date">Ngày Bắt Đầu:</label>
                <input type="datetime-local" name="start_date" required>
                
                <label for="end_date">Ngày Kết Thúc:</label>
                <input type="datetime-local" name="end_date" required>
                
                <button type="submit">Lưu Ưu Đãi</button>
            </form>
        </div>
    </div>
    <script>
        // Biến toàn cục để lưu giá sản phẩm hiện tại
        let currentPrice = 0;
        
        // Hàm tính giá sau khi giảm
        function calculateDiscountedPrice() {
            let discountPercent = document.getElementById('discount_percent').value;
            if (discountPercent && currentPrice) {
                // Tính giá sau khi giảm
                let discountAmount = currentPrice * discountPercent / 100;
                let discountedPrice = currentPrice - discountAmount;
                
                // Làm tròn xuống đến hàng nghìn gần nhất để có giá đẹp hơn
                discountedPrice = Math.floor(discountedPrice / 1000) * 1000;
                
                // Cập nhật giá trị vào trường input
                document.getElementById('discounted_price').value = discountedPrice;
                
                // Hiển thị thông tin giảm giá
                let savingsAmount = currentPrice - discountedPrice;
                let savingsPercent = (savingsAmount / currentPrice * 100).toFixed(1);
                
                // Hiển thị thông tin tiết kiệm nếu có phần tử HTML tương ứng
                let savingsInfo = document.getElementById('savings-info');
                if (savingsInfo) {
                    savingsInfo.innerHTML = 'Tiết kiệm: ' + formatCurrency(savingsAmount) + ' (' + savingsPercent + '%)';
                    savingsInfo.style.display = 'block';
                }
            }
        }
        
        // Hàm định dạng tiền tệ
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        }
        
        // Sự kiện khi chọn sản phẩm
        document.getElementById('product-select').addEventListener('change', function() {
            let selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                let price = selectedOption.getAttribute('data-price');
                let image = selectedOption.getAttribute('data-image');
                let remaining = selectedOption.getAttribute('data-remaining');
                
                // Cập nhật biến toàn cục
                currentPrice = parseFloat(price);
                
                // Cập nhật giao diện
                document.getElementById('product-image').src = image;
                
                // Hiển thị giá gốc
                let originalPriceElement = document.getElementById('original-price');
                if (originalPriceElement) {
                    originalPriceElement.textContent = 'Giá gốc: ' + formatCurrency(currentPrice);
                    originalPriceElement.style.display = 'block';
                }
                
                // Lưu trữ giá trị remaining vào data attribute để kiểm tra
                document.getElementById('discount_quantity').dataset.remaining = remaining;
                
                // Đặt lại trường số lượng khi thay đổi sản phẩm
                document.getElementById('discount_quantity').value = '';
                
                // Đặt giá trị tối đa cho số lượng
                document.getElementById('discount_quantity').setAttribute('max', remaining);
                
                // Hiển thị số lượng còn lại
                let remainingInfoElement = document.getElementById('remaining-info');
                if (remainingInfoElement) {
                    remainingInfoElement.textContent = 'Số lượng còn lại: ' + remaining;
                    remainingInfoElement.style.display = 'block';
                }
                
                // Tính lại giá sau khi giảm nếu đã có phần trăm giảm giá
                calculateDiscountedPrice();
            }
        });
        
        // Sự kiện khi nhập phần trăm giảm giá
        document.getElementById('discount_percent').addEventListener('input', calculateDiscountedPrice);
        
        // Kiểm tra số lượng không vượt quá số lượng còn lại
        document.getElementById('discount_quantity').addEventListener('input', function() {
            let remaining = parseInt(this.dataset.remaining);
            let quantity = parseInt(this.value);
            
            if (quantity > remaining) {
                alert('Số lượng sách được giảm giá không thể vượt quá số lượng sách còn lại (' + remaining + ')!');
                this.value = remaining;
            }
            
            if (quantity <= 0) {
                alert('Số lượng sách được giảm giá phải lớn hơn 0!');
                this.value = 1;
            }
        });
        
        // Kiểm tra form trước khi gửi
        document.querySelector('form').addEventListener('submit', function(e) {
            // Đảm bảo giá trị discounted_price đã được tính
            if (!document.getElementById('discounted_price').value) {
                e.preventDefault();
                alert('Vui lòng nhập phần trăm giảm giá để tính giá sau khi giảm!');
            }
        });
    </script>
</body>
</html>
