<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xóa Ưu Đãi</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('images/wave-background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: rgba(42, 42, 42, 0.9);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
        }
        select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: none;
            outline: none;
        }
        button {
            background: red;
            color: #fff;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Xóa Ưu Đãi</h2>
        <form action="" method="POST">
            <label for="discount_id">Chọn Ưu Đãi:</label>
            <select name="discount_id" required>
                <option value="">-- Chọn ưu đãi --</option>
                <?php
                require '../connect.php';
                $query = $pdo->query("SELECT d.discount_id, p.product_name FROM discount_products d JOIN products p ON d.product_id = p.product_id");
                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row['discount_id']}'>{$row['product_name']}</option>";
                }
                ?>
            </select>
            <button type="submit" name="delete">Xóa Ưu Đãi</button>
        </form>
    </div>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        require '../connect.php';
        $discount_id = $_POST['discount_id'];
        $stmt = $pdo->prepare("DELETE FROM discount_products WHERE discount_id=?");
        $stmt->execute([$discount_id]);
        echo "<script>alert('Ưu đãi đã được xóa!'); location.reload();</script>";
    }
    ?>
</body>
</html>
