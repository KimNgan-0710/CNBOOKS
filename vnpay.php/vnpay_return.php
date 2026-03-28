<?php
session_start();

// Lấy thông tin từ VNPAY
$vnp_ResponseCode = isset($_GET['vnp_ResponseCode']) ? $_GET['vnp_ResponseCode'] : '';
$vnp_TxnRef = isset($_GET['vnp_TxnRef']) ? $_GET['vnp_TxnRef'] : '';
$vnp_Amount = isset($_GET['vnp_Amount']) ? $_GET['vnp_Amount'] / 100 : 0; // Chia cho 100 vì VNPAY nhân với 100
$vnp_BankCode = isset($_GET['vnp_BankCode']) ? $_GET['vnp_BankCode'] : '';
$vnp_PayDate = isset($_GET['vnp_PayDate']) ? $_GET['vnp_PayDate'] : '';

// Lấy thông tin đơn hàng từ session
$orderInfo = isset($_SESSION['order']) ? $_SESSION['order'] : [];

// Kiểm tra kết quả thanh toán
if ($vnp_ResponseCode == '00') {
    // Thanh toán thành công
    
    // Lưu thông tin thanh toán vào session
    $_SESSION['payment_info'] = [
        'transaction_id' => $vnp_TxnRef,
        'amount' => $vnp_Amount,
        'bank_code' => $vnp_BankCode,
        'payment_date' => $vnp_PayDate,
        'status' => 'success'
    ];
    
    // Trong thực tế, bạn sẽ lưu thông tin đơn hàng vào database ở đây
    // Ví dụ: lưu vào bảng orders và order_items
    
    // Xóa giỏ hàng trong session
    unset($_SESSION['cart']);
    
    // Chuyển hướng đến trang xác nhận đơn hàng
    header('Location: http://localhost/EBooks/order_confirmation.php?id=' . $vnp_TxnRef . '&status=success');
} else {
    // Thanh toán thất bại
    
    // Lưu thông tin lỗi vào session
    $_SESSION['payment_error'] = [
        'code' => $vnp_ResponseCode,
        'message' => 'Thanh toán không thành công'
    ];
    
    // Chuyển hướng đến trang thông báo lỗi
    header('Location: http://localhost/EBooks/checkout.php?error=payment_failed');
}
exit();
