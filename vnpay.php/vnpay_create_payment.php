<?php
if (!isset($_SESSION)) {
    session_start();
}
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra xem có dữ liệu POST không
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Nếu không phải là POST request, chuyển hướng về trang checkout
    header('Location: ../checkout.php');
    exit();
}

/**
 * 
 *
 * @author CTT VNPAY
 */
require_once("./config.php");
date_default_timezone_set("Asia/Ho_Chi_Minh");
$date = date('Y-m-d H:i:s');

// Kiểm tra các tham số bắt buộc
if (!isset($_POST['id_don_hang']) || !isset($_POST['total_price'])) {
    // Nếu thiếu tham số bắt buộc, chuyển hướng về trang checkout với thông báo lỗi
    header('Location: ../checkout.php?error=missing_params');
    exit();
}

$vnp_TxnRef = $_POST['id_don_hang']; //Mã giao dịch thanh toán tham chiếu của merchant
$vnp_Amount = $_POST['total_price']; // Số tiền thanh toán
$vnp_Locale = 'vn'; //Ngôn ngữ chuyển hướng thanh toán
$vnp_BankCode = ''; //Mã phương thức thanh toán

// Lấy địa chỉ IP của khách hàng
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $vnp_IpAddr = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $vnp_IpAddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
}

// Nếu IP là ::1 (localhost), đổi thành 127.0.0.1
if ($vnp_IpAddr == '::1') {
    $vnp_IpAddr = '127.0.0.1';
}
$_SESSION['order']['payment_method'] = 'Thanh toán Online';
$_SESSION['order']['date'] = $date;
$_SESSION['order']['id_chi_tiet_san_pham'] = isset($_POST['id_chi_tiet_san_pham']) ? $_POST['id_chi_tiet_san_pham'] : '';
$_SESSION['order']['total_price'] = $_POST['total_price'];
$_SESSION['order']['id_don_hang'] = $_POST['id_don_hang'];
$_SESSION['order']['id_kh'] = isset($_POST['id_kh']) ? $_POST['id_kh'] : '';
$_SESSION['order']['dia_chi_giao'] = $_POST['dia_chi_giao'];
$_SESSION['order']['ho_ten'] = $_POST['ho_ten'];
$_SESSION['order']['phone'] = $_POST['phone'];
$_SESSION['order']['note'] = isset($_POST['note']) ? $_POST['note'] : '';
$_SESSION['order']['so_luong'] = isset($_POST['so_luong']) ? $_POST['so_luong'] : 1;
// Tạo mảng dữ liệu gửi đến VNPAY
$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => (int)$vnp_Amount * 100, // Số tiền thanh toán (nhân với 100 và chuyển thành số nguyên)
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => "Thanh toan GD:" . $vnp_TxnRef, // Nội dung
    "vnp_OrderType" => "other",
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_ExpireDate" => $expire
);

if (isset($vnp_BankCode) && $vnp_BankCode != "") {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}

ksort($inputData);
$query = "";
$i = 0;
$hashdata = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url = $vnp_Url . "?" . $query;
if (isset($vnp_HashSecret)) {
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}

// Chuyển hướng người dùng đến trang thanh toán VNPAY
header('Location: ' . $vnp_Url);
exit();
