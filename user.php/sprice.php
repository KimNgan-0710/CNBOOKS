<?php

include 'connect.php';

// Truy vấn lấy sản phẩm giảm giá đang có hiệu lực
$sql = "SELECT 
            p.product_id, 
            p.product_name, 
            p.description, 
            p.price, 
            p.image,
            d.discount_percent, 
            (p.price * (1 - d.discount_percent / 100)) AS discounted_price,
            d.start_date, 
            d.end_date
        FROM products p
        JOIN discount_products d ON p.product_id = d.product_id
        WHERE d.start_date <= NOW() AND d.end_date >= NOW()
        ORDER BY d.discount_percent DESC";

$stmt = $pdo->query($sql);
$discounted_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm giảm giá</title>
    <style>
        body {
            background: linear-gradient(120deg, rgb(217, 223, 235), rgb(133, 196, 221));
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }

        h2 {
            font-size: 28px;
            font-weight: bold;
            color: white;
            margin-bottom: 30px;
            /* Tăng khoảng cách giữa tiêu đề và sản phẩm */
        }

        /* Chia sản phẩm thành 5 cột */
        .container_main {
            width: 90%;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            /* 5 cột đều nhau */
            gap: 20px;
            /* Khoảng cách giữa các sản phẩm */
            justify-content: center;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        .product-card img {
            width: 100%;
            height: 250px;
            border-radius: 10px;
        }

        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: red;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        h3 {
            font-size: 18px;
            font-weight: bold;
            color: black;
            margin-top: 10px;
        }

        .price {
            margin-top: 10px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .old-price {
            text-decoration: line-through;
            color: gray;
            font-size: 14px;
        }

        .new-price {
            color: rgb(211, 32, 41);
            font-size: 20px;
            font-weight: bold;
        }

        .buy-button {
            margin-top: 10px;
            padding: 10px;
            background: pink;
            color: black;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
            
        }

        .buy-button:hover {
            background: red;
            color: white;
        }
    </style>
</head>

<body>
    <h2> THAM KHẢO THÊM SẢN PHẨM CỦA CNBOOKS</h2>
    <div class="container_main">
        <?php foreach ($discounted_products as $products): ?>
            <div class="product-card">
                <span class="discount-badge">-<?= htmlspecialchars($products['discount_percent']) ?>%</span>
                <img src="<?= htmlspecialchars($products['image']) ?>" alt="<?= htmlspecialchars($products['product_name']) ?>">
                <h3><?= htmlspecialchars($products['product_name']) ?></h3>
                <div class="price">
                    <span class="old-price"><?= number_format($products['price'], 0, ',', '.') ?>đ</span>
                    <br>
                    <span class="new-price" style="color:rgb(211, 32, 41); font-size: 20px; font-weight: bold;">
                        <?= number_format($products['discounted_price'], 0, ',', '.') ?>đ
                    </span>
                </div>
                <button class="buy-button" onclick="addToCart(
                        <?= $products['product_id'] ?>, 
                        '<?= htmlspecialchars($products['product_name']) ?>', 
                        <?= $products['discounted_price'] ?>, 
                        '<?= $products['image'] ?>'
                    )">🛍 Thêm vào giỏ hàng</button>

                </form>


            </div>
        <?php endforeach; ?>
    </div>
    
</body>

</html>