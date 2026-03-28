<?php
// Kiểm tra xem có tham số file không
if (!isset($_GET['file'])) {
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><title>Lỗi PDF</title><style>body{font-family:sans-serif;background:#fff;text-align:center;padding:60px;}h2{color:#e74c3c;}p{color:#555;}</style></head><body><h2>Rất tiếc, file PDF của tài liệu này không tồn tại.</h2><p>Vui lòng liên hệ quản trị viên để được hỗ trợ.</p></body></html>';
    exit;
}

$file = $_GET['file'];

// Kiểm tra xem file có tồn tại không
if (!file_exists($file)) {
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><title>Lỗi PDF</title><style>body{font-family:sans-serif;background:#fff;text-align:center;padding:60px;}h2{color:#e74c3c;}p{color:#555;}</style></head><body><h2>Rất tiếc, file PDF của tài liệu này không tồn tại.</h2><p>Vui lòng liên hệ quản trị viên để được hỗ trợ.</p></body></html>';
    exit;
}

// Kiểm tra xem file có phải là PDF không
$mime_type = mime_content_type($file);
if ($mime_type !== 'application/pdf') {
    echo '<!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><title>Lỗi PDF</title><style>body{font-family:sans-serif;background:#fff;text-align:center;padding:60px;}h2{color:#e74c3c;}p{color:#555;}</style></head><body><h2>Rất tiếc, file PDF của tài liệu này không tồn tại.</h2><p>Vui lòng liên hệ quản trị viên để được hỗ trợ.</p></body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Xem PDF</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .pdf-iframe {
            width: 100vw;
            height: 100vh;
            border: none;
            margin: 0;
            padding: 0;
            display: block;
        }
    </style>
</head>
<body>
    <iframe class="pdf-iframe" src="<?php echo htmlspecialchars($file); ?>"></iframe>
</body>
</html>