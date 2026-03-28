<?php
session_start();
require 'connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Lấy thông tin người dùng từ cơ sở dữ liệu
$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->bindParam(':username', $username);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy danh sách đơn hàng của user
$userId = $user['id'];
$orderList = [];
$orderStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC");
$orderStmt->bindParam(':user_id', $userId);
$orderStmt->execute();
$orderList = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý cập nhật thông tin
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Kiểm tra dữ liệu
    if (empty($fullname) || empty($phone) || empty($email)) {
        $message = 'Vui lòng điền đầy đủ thông tin cá nhân!';
        $messageType = 'error';
    } else {
        try {
            // Nếu người dùng muốn đổi mật khẩu
            if (!empty($current_password) && !empty($new_password)) {
                // Kiểm tra mật khẩu hiện tại
                if (!password_verify($current_password, $users['password'])) {
                    $message = 'Mật khẩu hiện tại không đúng!';
                    $messageType = 'error';
                } 
                // Kiểm tra mật khẩu mới và xác nhận mật khẩu
                elseif ($new_password !== $confirm_password) {
                    $message = 'Mật khẩu mới và xác nhận mật khẩu không khớp!';
                    $messageType = 'error';
                } 
                // Cập nhật thông tin và mật khẩu
                else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE users SET fullname = :fullname, phone = :phone, email = :email, password = :password WHERE username = :username");
                    $updateStmt->bindParam(':password', $hashed_password);
                    $updateStmt->bindParam(':fullname', $fullname);
                    $updateStmt->bindParam(':phone', $phone);
                    $updateStmt->bindParam(':email', $email);
                    $updateStmt->bindParam(':username', $username);
                    $updateStmt->execute();
                    
                    $message = 'Cập nhật thông tin và mật khẩu thành công!';
                    $messageType = 'success';
                    
                    // Cập nhật thông tin người dùng hiển thị
                    $user['fullname'] = $fullname;
                    $user['phone'] = $phone;
                    $user['email'] = $email;
                }
            } 
            // Chỉ cập nhật thông tin cá nhân
            else {
                $updateStmt = $pdo->prepare("UPDATE users SET fullname = :fullname, phone = :phone, email = :email WHERE username = :username");
                $updateStmt->bindParam(':fullname', $fullname);
                $updateStmt->bindParam(':phone', $phone);
                $updateStmt->bindParam(':email', $email);
                $updateStmt->bindParam(':username', $username);
                $updateStmt->execute();
                
                $message = 'Cập nhật thông tin thành công!';
                $messageType = 'success';
                
                // Cập nhật thông tin người dùng hiển thị
                $user['fullname'] = $fullname;
                $user['phone'] = $phone;
                $user['email'] = $email;
            }
        } catch (PDOException $e) {
            $message = 'Lỗi: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Bao gồm header
include 'header.php';
?>

<div class="container" style="margin-top: 100px; margin-bottom: 50px;">
    <div class="profile-container">
        <div class="profile-tabs">
            <button class="tab-btn active" onclick="showTab('info')"><i class="fas fa-user"></i> Thông tin cá nhân</button>
            <button class="tab-btn" onclick="showTab('orders')"><i class="fas fa-box"></i> Đơn hàng của tôi</button>
        </div>
        <div id="tab-info" class="tab-content active">
            <h2>Thông Tin Tài Khoản</h2>
            
            <?php if (!empty($message)): ?>
                <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-danger' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-info">
                        <h3><?= htmlspecialchars($user['username']) ?></h3>
                        <p><?= $user['role'] == 1 ? 'Quản trị viên' : 'Thành viên' ?></p>
                    </div>
                </div>
                
                <div class="profile-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="fullname">Họ và tên:</label>
                            <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Số điện thoại:</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <h4>Đổi mật khẩu</h4>
                        <p class="password-note">Để trống nếu không muốn đổi mật khẩu</p>
                        
                        <div class="form-group">
                            <label for="current_password">Mật khẩu hiện tại:</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Mật khẩu mới:</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-update">Cập nhật thông tin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="tab-orders" class="tab-content" style="display:none;">
            <h2>Đơn Hàng Của Tôi</h2>
            <?php if (empty($orderList)): ?>
                <p>Bạn chưa có đơn hàng nào.</p>
            <?php else: ?>
                <div class="order-list">
                    <?php foreach ($orderList as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <span class="order-id">#<?= htmlspecialchars($order['order_id']) ?></span>
                                <span class="order-date"><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></span>
                                <span class="order-total"><i class="fas fa-money-bill"></i> <?= number_format($order['total_price'], 0, ',', '.') ?>₫</span>
                            </div>
                            <div class="order-status">
                                <?php
                                $statusIcon = [
                                    'pending' => '<span class="status-icon pending" title="Chờ xác nhận">⏳</span>',
                                    'confirmed' => '<span class="status-icon confirmed" title="Đã xác nhận">✔️</span>',
                                    'packing' => '<span class="status-icon packing" title="Đang đóng gói">📦</span>',
                                    'shipping' => '<span class="status-icon shipping" title="Đang giao hàng">🚚</span>',
                                    'delivered' => '<span class="status-icon delivered" title="Đã giao hàng">✅</span>',
                                    'cancelled' => '<span class="status-icon cancelled" title="Đã hủy">❌</span>',
                                ];
                                $shippingStatus = $order['shipping_status'] ?? 'pending';
                                echo $statusIcon[$shippingStatus];
                                ?>
                                <span class="status-label">
                                    <?php
                                    switch ($shippingStatus) {
                                        case 'pending': echo 'Chờ xác nhận'; break;
                                        case 'confirmed': echo 'Đã xác nhận'; break;
                                        case 'packing': echo 'Đang đóng gói'; break;
                                        case 'shipping': echo 'Đang giao hàng'; break;
                                        case 'delivered': echo 'Đã giao hàng'; break;
                                        case 'cancelled': echo 'Đã hủy'; break;
                                        default: echo ucfirst($shippingStatus);
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="order-actions">
                                <a href="order_confirmation.php?id=<?= urlencode($order['order_id']) ?>" class="btn-view-detail"><i class="fas fa-eye"></i> Xem chi tiết</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showTab(tab) {
    document.getElementById('tab-info').style.display = (tab === 'info') ? 'block' : 'none';
    document.getElementById('tab-orders').style.display = (tab === 'orders') ? 'block' : 'none';
    var btns = document.querySelectorAll('.tab-btn');
    btns.forEach(function(btn, idx) {
        if ((tab === 'info' && idx === 0) || (tab === 'orders' && idx === 1)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}
</script>

<style>
    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    .profile-container {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        padding: 30px;
    }
    
    .profile-container h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #4a90e2;
    }
    
    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .profile-card {
        background-color: #f9f9f9;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .profile-header {
        background: linear-gradient(to right, #6ec1e4, #4a90e2);
        color: white;
        padding: 20px;
        display: flex;
        align-items: center;
    }
    
    .profile-avatar {
        font-size: 60px;
        margin-right: 20px;
    }
    
    .profile-info h3 {
        margin: 0;
        font-size: 24px;
    }
    
    .profile-info p {
        margin: 5px 0 0;
        opacity: 0.8;
    }
    
    .profile-body {
        padding: 30px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #555;
    }
    
    .form-group input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        transition: border-color 0.3s;
    }
    
    .form-group input:focus {
        border-color: #4a90e2;
        outline: none;
    }
    
    h4 {
        margin: 30px 0 10px;
        color: #4a90e2;
    }
    
    .password-note {
        color: #888;
        font-style: italic;
        margin-bottom: 15px;
    }
    
    .form-actions {
        margin-top: 30px;
        text-align: center;
    }
    
    .btn-update {
        background: linear-gradient(to right, #6ec1e4, #4a90e2);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .btn-update:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
    }
    
    .profile-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        justify-content: center;
    }
    .tab-btn {
        background: #f0f8ff;
        border: none;
        padding: 10px 25px;
        border-radius: 6px 6px 0 0;
        font-size: 16px;
        cursor: pointer;
        color: #1e90ff;
        font-weight: bold;
        transition: background 0.2s, color 0.2s;
    }
    .tab-btn.active, .tab-btn:hover {
        background: #1e90ff;
        color: #fff;
    }
    .tab-content { min-height: 200px; }
    .order-list { margin-top: 20px; }
    .order-item {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        padding: 18px 20px;
        margin-bottom: 18px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
    }
    .order-info { display: flex; gap: 18px; align-items: center; }
    .order-id { font-weight: bold; color: #1e90ff; }
    .order-date, .order-total { color: #555; font-size: 15px; }
    .order-status { display: flex; align-items: center; gap: 8px; }
    .status-icon { font-size: 22px; }
    .status-icon.pending { color: #f39c12; }
    .status-icon.confirmed { color: #2980b9; }
    .status-icon.packing { color: #8e44ad; }
    .status-icon.shipping { color: #e67e22; }
    .status-icon.delivered { color: #27ae60; }
    .status-icon.cancelled { color: #e74c3c; }
    .status-label { font-weight: bold; font-size: 15px; }
    .order-actions { margin-left: 15px; }
    .btn-view-detail {
        background: #1e90ff;
        color: #fff;
        padding: 7px 16px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 15px;
        transition: background 0.2s;
    }
    .btn-view-detail:hover { background: #155fa0; }
</style>

<?php
// Bao gồm footer nếu có
if (file_exists('footer.php')) {
    include 'footer.php';
}
?>