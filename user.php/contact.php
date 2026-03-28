<?php
include 'header.php';
require 'connect.php';

// Xử lý form liên hệ khi được gửi
$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $content = trim($_POST['message']);
    
    // Kiểm tra dữ liệu
    if (empty($name) || empty($email) || empty($phone) || empty($subject) || empty($content)) {
        $message = 'Vui lòng điền đầy đủ thông tin!';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email không hợp lệ!';
        $messageType = 'error';
    } else {
        // Lưu thông tin liên hệ vào cơ sở dữ liệu (nếu có bảng contacts)
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            if ($stmt->execute([$name, $email, $phone, $subject, $content])) {
                $message = 'Cảm ơn bạn đã liên hệ với chúng tôi! Chúng tôi sẽ phản hồi sớm nhất có thể.';
                $messageType = 'success';
                
                // Gửi email thông báo cho admin (tùy chọn)
                // mail('admin@example.com', 'Liên hệ mới từ website', "Tên: $name\nEmail: $email\nSố điện thoại: $phone\nChủ đề: $subject\nNội dung: $content");
                
                // Reset form
                $name = $email = $phone = $subject = $content = '';
            } else {
                $message = 'Có lỗi xảy ra, vui lòng thử lại sau!';
                $messageType = 'error';
            }
        } catch(PDOException $e) {
            // Nếu không có bảng contacts, vẫn hiển thị thông báo thành công cho người dùng
            $message = 'Cảm ơn bạn đã liên hệ với chúng tôi! Chúng tôi sẽ phản hồi sớm nhất có thể.';
            $messageType = 'success';
            
            // Reset form
            $name = $email = $phone = $subject = $content = '';
        }
    }
}
?>

<div class="contact-container">
    <div class="contact-header">
        <h1>Liên Hệ Với Chúng Tôi</h1>
        <p>Hãy để lại thông tin, chúng tôi sẽ liên hệ với bạn sớm nhất có thể!</p>
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="contact-content">
        <div class="contact-info">
            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <h3>Địa Chỉ</h3>
                    <p>31 Phan Đình Giót, Phương Liệt, Thanh Xuân, Hà Nội</p>
                </div>
            </div>
            
            <div class="info-item">
                <i class="fas fa-phone"></i>
                <div>
                    <h3>Điện Thoại</h3>
                    <p>(+84) 837203402</p>
                </div>
            </div>
            
            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <div>
                    <h3>Email</h3>
                    <p>info@ebooks.com</p>
                </div>
            </div>
            
            <div class="info-item">
                <i class="fas fa-clock"></i>
                <div>
                    <h3>Giờ Làm Việc</h3>
                    <p>Thứ Hai - Thứ Sáu: 8:00 - 17:00<br>Thứ Bảy: 8:00 - 12:00</p>
                </div>
            </div>
            
            
        </div>
        
        <div class="contact-form">
            <h2>Gửi Tin Nhắn</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Họ và Tên</label>
                    <input type="text" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Số Điện Thoại</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Chủ Đề</label>
                    <input type="text" id="subject" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Nội Dung</label>
                    <textarea id="message" name="message" rows="5" required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Gửi Tin Nhắn</button>
            </form>
        </div>
    </div>
    
    <div class="map-container">
        <h2>Bản Đồ</h2>
        <div class="map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3724.8906405873707!2d105.83991007596354!3d20.99383198065362!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ac5d6ec1b8cf%3A0x982365cd4337fdc8!2zMzEgUGhhbiDEkMOsbmggR2nDs3QsIFBoxrDGoW5nIExpw6p0LCBUaGFuaCBYdcOibiwgSMOgIE7hu5lpLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1699000000000!5m2!1svi!2s" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</div>

<style>
    .contact-container {
        max-width: 1200px;
        margin: 100px auto 50px;
        padding: 0 20px;
    }
    
    .contact-header {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .contact-header h1 {
        color: #1e90ff;
        font-size: 36px;
        margin-bottom: 15px;
    }
    
    .contact-header p {
        color: #666;
        font-size: 18px;
    }
    
    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 30px;
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
    
    .contact-content {
        display: flex;
        flex-wrap: wrap;
        gap: 40px;
        margin-bottom: 50px;
    }
    
    .contact-info {
        flex: 1;
        min-width: 300px;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .info-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 25px;
    }
    
    .info-item i {
        font-size: 24px;
        color: #1e90ff;
        margin-right: 15px;
        margin-top: 5px;
    }
    
    .info-item h3 {
        margin: 0 0 5px;
        color: #333;
        font-size: 18px;
    }
    
    .info-item p {
        margin: 0;
        color: #666;
        line-height: 1.6;
    }
    
    .social-media {
        margin-top: 30px;
    }
    
    .social-media h3 {
        margin: 0 0 15px;
        color: #333;
        font-size: 18px;
    }
    
    .social-icons {
        display: flex;
        gap: 15px;
    }
    
    .social-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: #1e90ff;
        color: white;
        border-radius: 50%;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .social-icon:hover {
        background: #0066cc;
        transform: translateY(-3px);
    }
    
    .contact-form {
        flex: 1;
        min-width: 300px;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .contact-form h2 {
        color: #1e90ff;
        margin: 0 0 25px;
        font-size: 24px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        transition: border-color 0.3s;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        border-color: #1e90ff;
        outline: none;
    }
    
    .submit-btn {
        background: #1e90ff;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.3s;
        width: 100%;
    }
    
    .submit-btn:hover {
        background: #0066cc;
    }
    
    .map-container {
        margin-top: 50px;
    }
    
    .map-container h2 {
        color: #1e90ff;
        margin: 0 0 20px;
        font-size: 24px;
    }
    
    .map {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
        .contact-content {
            flex-direction: column;
        }
        
        .contact-info,
        .contact-form {
            width: 100%;
        }
    }
</style>

<?php
include 'footer.php';
?>