<?php
// Kết nối database
require 'connect.php';

// Lấy tên truyện qua URL (?product_name= hoặc ?product=)
$product_name = $_GET['product_name'] ?? $_GET['product'] ?? '';

// Kiểm tra xem có tham số product_name hoặc product không
if (empty($product_name)) {
    echo "Không có tên sách được cung cấp.";
    exit;
}

// Lấy thông tin sách từ bảng storybooks
$stmt_info = $pdo->prepare("SELECT * FROM storybooks WHERE product_name = ? LIMIT 1");
$stmt_info->execute([$product_name]);
$info = $stmt_info->fetch(PDO::FETCH_ASSOC);

// Nếu không có thông tin, báo lỗi
if (!$info) {
    echo "Không tìm thấy thông tin sách.";
    exit;
}

// Kiểm tra xem sách có trong bảng readbooks không và cập nhật lượt đọc
$type_name = '';
$stmt_check = $pdo->prepare("SELECT type_name FROM readbooks WHERE product_name = ? LIMIT 1");
$stmt_check->execute([$product_name]);
$book_info = $stmt_check->fetch(PDO::FETCH_ASSOC);

if ($book_info) {
    $type_name = $book_info['type_name'];
    
    // Cập nhật lượt đọc
    $stmt_update = $pdo->prepare("UPDATE readbooks SET read_count = read_count + 1 WHERE product_name = ? AND type_name = ?");
    $stmt_update->execute([$product_name, $type_name]);
}

// Lấy tất cả nội dung và ảnh từ bảng storybooks cho sách này
$stmt_read = $pdo->prepare("SELECT * FROM storybooks WHERE product_name = ? ORDER BY id ASC");
$stmt_read->execute([$product_name]);
$rows = $stmt_read->fetchAll(PDO::FETCH_ASSOC);

// Xử lý nội dung và ảnh
if (!empty($rows)) {
    // Lấy dữ liệu từ bản ghi đầu tiên
    $book = $rows[0];
    
    // Tách nội dung thành các đoạn
    $paragraphs = explode("\r\n", $book['content']);
    
    // Tách URL ảnh thành mảng
    $images = [];
    if (!empty($book['image'])) {
        $images = array_map('trim', explode(';', $book['image']));
    }
    
    // Đảm bảo có đủ ảnh cho mỗi đoạn văn
    while (count($images) < count($paragraphs)) {
        $images[] = '';
    }
    
    // Đảm bảo có đủ đoạn văn cho mỗi ảnh
    while (count($paragraphs) < count($images)) {
        $paragraphs[] = '';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($info['product_name']) ?></title>
  <style>
    body {
      background-color: #ffffff;
      font-family: 'Comic Sans MS', cursive, sans-serif;
      margin: 0;
      padding: 0;
    }
    .header {
      background: linear-gradient(to right, #f8b3da, #a0d8ef);
      padding: 30px;
      text-align: center;
      color: #fff;
    }
    .header h1 {
      margin: 0;
      font-size: 36px;
    }
    .info {
      margin-top: 10px;
      font-size: 18px;
    }
    .content-section {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 40px 5%;
      background-color: #fefefe;
      margin-bottom: 30px;
    }
    .content-section:nth-child(even) {
      background-color: #f0f8ff;
    }
    .content-text {
      width: 45%;
      padding: 25px;
      font-size: 20px;
      background-color: #ffeef8;
      border-radius: 20px;
      box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
      margin: 10px;
    }
    .content-image {
      width: 45%;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 10px;
    }
    .content-image img {
      max-width: 450px;
      max-height: 600px;
      width: auto;
      height: auto;
      border-radius: 20px;
      box-shadow: 0 0 20px #a0d8ef;
      transition: transform 0.3s ease;
    }
    .content-image img:hover {
      transform: scale(1.05);
    }
    .image-placeholder {
      width: 400px;
      height: 400px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f8f9fa;
      border-radius: 20px;
      color: #6c757d;
      font-style: italic;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .image-error {
      width: 400px;
      height: 400px;
      border: 2px solid #ff6b6b;
      background-color: #ffe0e0;
      padding: 20px;
      border-radius: 20px;
      text-align: center;
      color: #d63031;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      font-size: 18px;
    }
    

    
    /* Content styles */
    .content-text p {
      text-align: justify;
      line-height: 1.8;
      margin-bottom: 15px;
      text-indent: 30px;
      font-size: 20px;
      color: #333;
    }
  </style>
</head>
<body>

<div class="header">
  <h1><?= htmlspecialchars($info['product_name']) ?></h1>
  <div class="info">
    Năm xuất bản: <?= htmlspecialchars($info['publish_year'] ?? 'Không có thông tin') ?> |
    Tái bản lần thứ: <?= htmlspecialchars($info['edition'] ?? 'Không có thông tin') ?>
  </div>
</div>

<?php if (!empty($paragraphs) && !empty($images)): ?>
  <?php foreach ($paragraphs as $index => $paragraph): ?>
    <div class="content-section">
      <?php if ($index % 2 == 0): // Ảnh bên trái, văn bản bên phải cho các đoạn chẵn ?>
        <div class="content-image">
          <?php if (!empty($images[$index])): ?>
            <img 
              src="<?= htmlspecialchars($images[$index]) ?>" 
              alt="Ảnh minh họa <?= $index + 1 ?>" 
              onerror="this.onerror=null; this.parentNode.innerHTML='<div class=\'image-error\'>Không thể tải ảnh.<br>URL: <?= htmlspecialchars(addslashes($images[$index])) ?></div>';"
            >
          <?php else: ?>
            <div class="image-placeholder">Không có ảnh cho phần này</div>
          <?php endif; ?>
        </div>
        <div class="content-text">
          <p><?= htmlspecialchars($paragraph) ?></p>
        </div>
      <?php else: // Văn bản bên trái, ảnh bên phải cho các đoạn lẻ ?>
        <div class="content-text">
          <p><?= htmlspecialchars($paragraph) ?></p>
        </div>
        <div class="content-image">
          <?php if (!empty($images[$index])): ?>
            <img 
              src="<?= htmlspecialchars($images[$index]) ?>" 
              alt="Ảnh minh họa <?= $index + 1 ?>" 
              onerror="this.onerror=null; this.parentNode.innerHTML='<div class=\'image-error\'>Không thể tải ảnh.<br>URL: <?= htmlspecialchars(addslashes($images[$index])) ?></div>';"
            >
          <?php else: ?>
            <div class="image-placeholder">Không có ảnh cho phần này</div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <div class="content-section">
    <div class="content-text">
      <p>Không có nội dung hoặc ảnh cho sách này.</p>
    </div>
  </div>
<?php endif; ?>

</body>
</html>
