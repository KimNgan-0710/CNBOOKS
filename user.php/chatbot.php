<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userMessage = trim($_POST["message"]);
    
    // Tìm câu hỏi trong bảng FAQ
    $stmt = $pdo->prepare("SELECT answer FROM faq WHERE question LIKE ?");
    $stmt->execute(["%$userMessage%"]);
    $faqAnswer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($faqAnswer) {
        echo $faqAnswer['answer'];
    } else {
        // Nếu không có trong FAQ, tìm trong bảng sản phẩm
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_name LIKE ?");
        $stmt->execute(["%$userMessage%"]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            echo "<b>📖 Tên sách:</b> " . htmlspecialchars($product['product_name']) . "<br>";
            echo "<b>💰 Giá:</b> " . number_format($product['price'], 0, ',', '.') . " VND<br>";
            if (!empty($product['image'])) {
                echo "<br><img src='" . htmlspecialchars($product['image']) . "' width='120'>";
            }
        } else {
            echo "❌ Xin lỗi, mình chưa hiểu câu hỏi này. Bạn có thể thử hỏi lại!";
        }
    }
}
?>
