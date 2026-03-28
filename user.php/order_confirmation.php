<?php
session_start();
include 'header.php';

// Kiểm tra xem có ID đơn hàng không
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$orderId = $_GET['id'];
$paymentStatus = isset($_GET['status']) ? $_GET['status'] : '';

// Lấy thông tin thanh toán từ session nếu có
$paymentInfo = isset($_SESSION['payment_info']) ? $_SESSION['payment_info'] : null;

// Lấy thông tin đơn hàng từ database
require 'connect.php';
$orderData = null;
$orderItems = null;

try {
    // Truy vấn thông tin đơn hàng theo order_id (string)
    $orderStmt = $pdo->prepare("
        SELECT o.*, u.username 
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.order_id = :order_id
    ");
    $orderStmt->bindParam(':order_id', $orderId);
    $orderStmt->execute();
    $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($orderData) {
        // Lấy id INT thực tế của đơn hàng
        $orderDbId = $orderData['id'];
        // Truy vấn chi tiết đơn hàng theo id INT
        $itemsStmt = $pdo->prepare("
            SELECT od.*, p.product_name as title, p.image as image_url
            FROM order_details od
            JOIN products p ON od.product_id = p.product_id
            WHERE od.order_id = :order_id
        ");
        $itemsStmt->bindParam(':order_id', $orderDbId);
        $itemsStmt->execute();
        $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Ghi log lỗi
    error_log("Lỗi truy vấn đơn hàng: " . $e->getMessage());
}
?>

<div style="margin-top: 100px; padding: 20px; max-width: 800px; margin-left: auto; margin-right: auto;">
    <div class="confirmation-container" style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-align: center;">
        <div class="success-icon" style="margin-bottom: 20px;">
            <i class="fas fa-check-circle" style="font-size: 60px; color: #4CAF50;"></i>
        </div>
        
        <h1 style="color: #1e90ff; margin-bottom: 20px;">Đặt hàng thành công!</h1>
        
        <p style="font-size: 18px; margin-bottom: 10px;">Cảm ơn bạn đã mua sắm tại CNBooks.</p>
        
        <div class="order-details" style="margin: 30px 0; text-align: left; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h2 style="color: #1e90ff; margin-bottom: 15px; font-size: 20px;">Chi tiết đơn hàng</h2>
            
            <div style="margin-bottom: 10px;">
                <strong>Mã đơn hàng:</strong> <span><?php echo htmlspecialchars($orderId); ?></span>
            </div>
            
            <div style="margin-bottom: 10px;">
                <strong>Ngày đặt hàng:</strong> <span><?php echo $orderData ? date('d/m/Y H:i', strtotime($orderData['order_date'])) : ''; ?></span>
            </div>
            
            <div style="margin-bottom: 10px;">
                <strong>Phương thức thanh toán:</strong> <span><?php echo $orderData ? htmlspecialchars($orderData['payment_method']) : ''; ?></span>
            </div>
            
            <div style="margin-bottom: 10px;">
                <strong>Trạng thái thanh toán:</strong> <span style="color: #4CAF50; font-weight: bold;">
                    <?php 
                    if ($orderData) {
                        switch ($orderData['status']) {
                            case 'completed': echo 'Đã thanh toán'; break;
                            case 'pending': echo 'Chờ thanh toán'; break;
                            case 'processing': echo 'Đang xử lý'; break;
                            case 'cancelled': echo 'Đã hủy'; break;
                            default: echo htmlspecialchars($orderData['status']);
                        }
                    }
                    ?>
                </span>
            </div>
            
            <?php if ($paymentInfo): ?>
            <div style="margin-top: 15px; background-color: #f0f8ff; padding: 10px; border-radius: 5px; border-left: 4px solid #1e90ff;">
                <h3 style="color: #1e90ff; margin-bottom: 10px; font-size: 16px;">Chi tiết thanh toán</h3>
                <div style="margin-bottom: 5px;">
                    <strong>Mã giao dịch:</strong> <?php echo htmlspecialchars($paymentInfo['transaction_id']); ?>
                </div>
                <div style="margin-bottom: 5px;">
                    <strong>Số tiền:</strong> <?php echo number_format($paymentInfo['amount'], 0, ',', '.') . ' ₫'; ?>
                </div>
                <?php if (!empty($paymentInfo['bank_code'])): ?>
                <div style="margin-bottom: 5px;">
                    <strong>Ngân hàng:</strong> <?php echo htmlspecialchars($paymentInfo['bank_code']); ?>
                </div>
                <?php endif; ?>
                <div style="margin-bottom: 5px;">
                    <strong>Thời gian:</strong> <?php echo htmlspecialchars($paymentInfo['payment_date']); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div style="margin-top: 20px;">
                <h3 style="color: #1e90ff; margin-bottom: 10px; font-size: 18px;">Thông tin giao hàng</h3>
                
                <div style="margin-bottom: 5px;">
                    <strong>Họ tên:</strong> <span><?php echo $orderData ? htmlspecialchars($orderData['name']) : ''; ?></span>
                </div>
                
                <div style="margin-bottom: 5px;">
                    <strong>Email:</strong> <span><?php echo $orderData ? htmlspecialchars($orderData['email']) : ''; ?></span>
                </div>
                
                <div style="margin-bottom: 5px;">
                    <strong>Số điện thoại:</strong> <span><?php echo $orderData ? htmlspecialchars($orderData['phone']) : ''; ?></span>
                </div>
                
                <div style="margin-bottom: 5px;">
                    <strong>Địa chỉ:</strong> <span><?php echo $orderData ? htmlspecialchars($orderData['address']) : ''; ?></span>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <h3 style="color: #1e90ff; margin-bottom: 10px; font-size: 18px;">Sản phẩm đã mua</h3>
                <?php if (!empty($orderItems)): ?>
                    <?php $totalPrice = 0; ?>
                    <?php foreach ($orderItems as $item): ?>
                        <?php $itemTotal = $item['price'] * $item['quantity']; $totalPrice += $itemTotal; ?>
                        <div style="display: flex; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                            <div style="width: 60px; margin-right: 10px;">
                                <img src="<?php echo !empty($item['image_url']) ? $item['image_url'] : 'images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" style="width: 100%; border-radius: 5px;">
                            </div>
                            <div style="flex-grow: 1;">
                                <div style="font-weight: bold;"><?php echo htmlspecialchars($item['title']); ?></div>
                                <div><?php echo number_format($item['price'], 0, ',', '.'); ?>₫ x <?php echo $item['quantity']; ?></div>
                            </div>
                            <div style="font-weight: bold; color: #1e90ff;">
                                <?php echo number_format($itemTotal, 0, ',', '.'); ?>₫
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div style="margin-top: 15px; text-align: right; font-weight: bold; font-size: 18px;">
                        <span>Tổng cộng: </span>
                        <span style="color: #1e90ff;"><?php echo number_format($totalPrice, 0, ',', '.'); ?>₫</span>
                    </div>
                <?php else: ?>
                    <p>Không có thông tin chi tiết sản phẩm.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="actions" style="margin-top: 30px;">
            <a href="index.php" style="display: inline-block; padding: 12px 25px; background: #1e90ff; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px; transition: background 0.3s ease;">
                <i class="fas fa-home"></i> Trang chủ
            </a>
            <a href="product.php" style="display: inline-block; padding: 12px 25px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; transition: background 0.3s ease;">
                <i class="fas fa-shopping-cart"></i> Tiếp tục mua sắm
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lấy thông tin đơn hàng từ localStorage
        const localOrderData = JSON.parse(localStorage.getItem('lastOrder'));
        
        // Kiểm tra xem có thông tin từ VNPAY không
        const urlParams = new URLSearchParams(window.location.search);
        const paymentStatus = urlParams.get('status');
        
        <?php if ($orderData): ?>
        // Nếu có dữ liệu từ database
        displayOrderFromDatabase();
        // Xóa giỏ hàng
        localStorage.removeItem('cart');
        <?php else: ?>
        // Nếu không có dữ liệu từ database, sử dụng dữ liệu từ localStorage
        if (paymentStatus === 'success') {
            // Nếu thanh toán thành công qua VNPAY
            document.getElementById('order-date').textContent = new Date().toLocaleString('vi-VN');
            document.getElementById('payment-method').textContent = 'Thanh toán qua VNPAY';
            document.getElementById('payment-status').textContent = 'Đã thanh toán';
            
            // Lấy thông tin đơn hàng từ localStorage nếu có
            if (localOrderData) {
                // Hiển thị thông tin khách hàng
                document.getElementById('customer-name').textContent = localOrderData.name;
                document.getElementById('customer-email').textContent = localOrderData.email;
                document.getElementById('customer-phone').textContent = localOrderData.phone;
                document.getElementById('customer-address').textContent = localOrderData.address;
                
                // Xóa giỏ hàng
                localStorage.removeItem('cart');
            }
        } else if (localOrderData) {
            // Hiển thị thông tin đơn hàng từ localStorage
            document.getElementById('order-date').textContent = new Date().toLocaleString('vi-VN');
            
            // Hiển thị phương thức thanh toán
            let paymentMethodText = '';
            switch (localOrderData.paymentMethod) {
                case 'cod':
                    paymentMethodText = 'Thanh toán khi nhận hàng (COD)';
                    break;
                case 'vnpay':
                    paymentMethodText = 'Thanh toán qua VNPAY';
                    break;
                case 'momo':
                    paymentMethodText = 'Thanh toán qua MoMo';
                    break;
                case 'credit':
                    paymentMethodText = 'Thanh toán qua thẻ tín dụng/ghi nợ';
                    break;
                default:
                    paymentMethodText = localOrderData.paymentMethod;
            }
            document.getElementById('payment-method').textContent = paymentMethodText;
            
            // Hiển thị trạng thái thanh toán
            document.getElementById('payment-status').textContent = 'Đã thanh toán';
            
            // Hiển thị thông tin khách hàng
            document.getElementById('customer-name').textContent = localOrderData.name;
            document.getElementById('customer-email').textContent = localOrderData.email;
            document.getElementById('customer-phone').textContent = localOrderData.phone;
            document.getElementById('customer-address').textContent = localOrderData.address;
            
            // Hiển thị danh sách sản phẩm
            const orderItemsElement = document.getElementById('order-items');
            
            if (localOrderData && localOrderData.items && localOrderData.items.length > 0) {
                let orderItemsHTML = '';
                let totalPrice = 0;
                
                localOrderData.items.forEach(item => {
                    const itemTotal = item.price * item.quantity;
                    totalPrice += itemTotal;
                    
                    orderItemsHTML += `
                    <div style="display: flex; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                        <div style="width: 60px; margin-right: 10px;">
                            <img src="${item.image}" alt="${item.name}" style="width: 100%; border-radius: 5px;">
                        </div>
                        <div style="flex-grow: 1;">
                            <div style="font-weight: bold;">${item.name}</div>
                            <div>${formatCurrency(item.price)} x ${item.quantity}</div>
                        </div>
                        <div style="font-weight: bold; color: #1e90ff;">
                            ${formatCurrency(itemTotal)}
                        </div>
                    </div>
                    `;
                });
                
                orderItemsElement.innerHTML = orderItemsHTML;
                
                // Hiển thị tổng tiền
                document.getElementById('order-total').textContent = formatCurrency(totalPrice);
            } else {
                // Nếu không có thông tin sản phẩm từ localStorage, hiển thị thông báo
                orderItemsElement.innerHTML = '<p>Không có thông tin chi tiết sản phẩm.</p>';
                
                // Nếu có thông tin thanh toán từ VNPAY, hiển thị số tiền
                <?php if ($paymentInfo): ?>
                document.getElementById('order-total').textContent = '<?php echo number_format($paymentInfo['amount'], 0, ',', '.') . ' ₫'; ?>';
                <?php else: ?>
                document.getElementById('order-total').textContent = 'Không có thông tin';
                <?php endif; ?>
            }
        } else {
            // Nếu không có thông tin đơn hàng, chuyển hướng về trang chủ
            window.location.href = 'index.php';
        }
        <?php endif; ?>
        
        // Hàm hiển thị đơn hàng từ database
        function displayOrderFromDatabase() {
            <?php if ($orderData): ?>
            // Hiển thị ngày đặt hàng
            document.getElementById('order-date').textContent = '<?php echo date('d/m/Y H:i', strtotime($orderData['order_date'])); ?>';
            
            // Hiển thị phương thức thanh toán
            let paymentMethodText = '';
            switch ('<?php echo $orderData['payment_method']; ?>') {
                case 'cod':
                    paymentMethodText = 'Thanh toán khi nhận hàng (COD)';
                    break;
                case 'vnpay':
                    paymentMethodText = 'Thanh toán qua VNPAY';
                    break;
                case 'momo':
                    paymentMethodText = 'Thanh toán qua MoMo';
                    break;
                case 'credit':
                    paymentMethodText = 'Thanh toán qua thẻ tín dụng/ghi nợ';
                    break;
                default:
                    paymentMethodText = '<?php echo $orderData['payment_method']; ?>';
            }
            document.getElementById('payment-method').textContent = paymentMethodText;
            
            // Hiển thị trạng thái thanh toán
            let statusText = '';
            switch ('<?php echo $orderData['status']; ?>') {
                case 'completed':
                    statusText = 'Đã thanh toán';
                    break;
                case 'pending':
                    statusText = 'Chờ thanh toán';
                    break;
                case 'processing':
                    statusText = 'Đang xử lý';
                    break;
                case 'cancelled':
                    statusText = 'Đã hủy';
                    break;
                default:
                    statusText = '<?php echo $orderData['status']; ?>';
            }
            document.getElementById('payment-status').textContent = statusText;
            
            // Hiển thị thông tin khách hàng
            document.getElementById('customer-name').textContent = '<?php echo htmlspecialchars($orderData['name']); ?>';
            document.getElementById('customer-email').textContent = '<?php echo htmlspecialchars($orderData['email']); ?>';
            document.getElementById('customer-phone').textContent = '<?php echo htmlspecialchars($orderData['phone']); ?>';
            document.getElementById('customer-address').textContent = '<?php echo htmlspecialchars($orderData['address']); ?>';
            
            // Hiển thị danh sách sản phẩm
            const orderItemsElement = document.getElementById('order-items');
            
            <?php if (!empty($orderItems)): ?>
            let orderItemsHTML = '';
            let totalPrice = 0;
            
            <?php foreach ($orderItems as $item): ?>
            const itemTotal = <?php echo $item['price'] * $item['quantity']; ?>;
            totalPrice += itemTotal;
            
            orderItemsHTML += `
            <div style="display: flex; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                <div style="width: 60px; margin-right: 10px;">
                    <img src="<?php echo !empty($item['image_url']) ? $item['image_url'] : 'images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" style="width: 100%; border-radius: 5px;">
                </div>
                <div style="flex-grow: 1;">
                    <div style="font-weight: bold;"><?php echo htmlspecialchars($item['title']); ?></div>
                    <div>${formatCurrency(<?php echo $item['price']; ?>)} x <?php echo $item['quantity']; ?></div>
                </div>
                <div style="font-weight: bold; color: #1e90ff;">
                    ${formatCurrency(itemTotal)}
                </div>
            </div>
            `;
            <?php endforeach; ?>
            
            orderItemsElement.innerHTML = orderItemsHTML;
            
            // Hiển thị tổng tiền
            document.getElementById('order-total').textContent = formatCurrency(<?php echo $orderData['total_price']; ?>);
            <?php else: ?>
            // Nếu không có chi tiết đơn hàng
            orderItemsElement.innerHTML = '<p>Không có thông tin chi tiết sản phẩm.</p>';
            document.getElementById('order-total').textContent = formatCurrency(<?php echo $orderData['total_price']; ?>);
            <?php endif; ?>
            <?php endif; ?>
        }
    });
    
    // Hàm định dạng tiền tệ
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }
</script>

<?php
include 'footer.php';
?>