<?php
session_start();
require_once 'connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
    header('Location: pdf_books.php');
    exit;
}

// Kiểm tra xem có file được upload không
if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = 'Vui lòng chọn file PDF để tải lên';
    header('Location: pdf_books.php');
    exit;
}

// Kiểm tra product_id
if (empty($_POST['product_id'])) {
    $_SESSION['error'] = 'Vui lòng chọn sách để gắn file PDF';
    header('Location: pdf_books.php');
    exit;
}
$product_id = (int)$_POST['product_id'];

// Kiểm tra file PDF
$pdf_file = $_FILES['pdf_file'];
$pdf_info = pathinfo($pdf_file['name']);

if (strtolower($pdf_info['extension']) !== 'pdf') {
    $_SESSION['error'] = 'Chỉ chấp nhận file PDF';
    header('Location: pdf_books.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Lấy tên sách để hiển thị thông báo (không bắt buộc)
    $stmt = $pdo->prepare("SELECT product_name FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    // Tạo thư mục nếu chưa tồn tại
    $upload_dir = 'pdf_books/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Đường dẫn file PDF với tên gốc
    $pdf_path = $upload_dir . $pdf_file['name'];
    if (!move_uploaded_file($pdf_file['tmp_name'], $pdf_path)) {
        throw new Exception('Không thể lưu file PDF');
    }

    // Cập nhật trường file_pdf cho sách đã chọn
    $stmt = $pdo->prepare("UPDATE products SET file_pdf = ? WHERE product_id = ?");
    $stmt->execute([$pdf_file['name'], $product_id]);

    $pdo->commit();
    $_SESSION['success'] = 'Đã gắn file PDF cho sách: ' . htmlspecialchars($book['product_name']);
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
}

header('Location: pdf_books.php');
exit;
?>