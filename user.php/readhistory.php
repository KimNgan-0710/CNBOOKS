<?php
include 'connect.php'; // Kết nối PDO

$sql = "SELECT * FROM readbooks WHERE type_name = :type_name";
$stmt = $pdo->prepare($sql);
$stmt->execute(['type_name' => 'lịch sử']);
$books = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Giao diện đọc sách</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #fff0f5;
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #ff69b4;
            margin-bottom: 40px;
            margin-top: 80px;
        }

        .book-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            padding-left: 50px;
        }

        .book {
            position: relative;
            cursor: pointer;
        }

        .book img {
            width: 100%;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .book:hover img {
            transform: scale(1.05);
        }

        .tooltip-box {
            display: none;
            position: absolute;
            top: 0;
            left: 100%;
            margin-left: 10px;
            width: 250px;
            background: #fff;
            color: #333;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            padding: 15px;
            z-index: 100;
            font-size: 14px;
        }

        .book:hover .tooltip-box {
            display: block;
        }

        .tooltip-box strong {
            color: #ff69b4;
        }
    </style>
</head>

<body>
    <?php
    include 'header.php';
    echo '<div style="margin-top: 50px;"></div>';?>
    <h1>📚 Tài Liệu Lịch Sử</h1>
    <div class="book-grid">
        <?php foreach ($books as $book): ?>
            <div class="book">
                <a href="readdetail.php?product_name=<?= urlencode($book['product_name']) ?>">
                    <img src="<?= htmlspecialchars($book['image']) ?>" alt="<?= htmlspecialchars($book['product_name']) ?>" style="width: 200px; border-radius: 15px;">
                </a>
                <div class="tooltip-box">
                    <p><strong>Tên sách:</strong> <?php echo htmlspecialchars($book['product_name']); ?></p>
                    <p><strong>Tác giả:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                    <p><strong>Nội dung:</strong> <?php echo htmlspecialchars(mb_strimwidth($book['content'], 0, 150, "...")); ?></p>
                    <p><strong>Lượt đọc:</strong> <?php echo htmlspecialchars($book['read_count']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>

</html>