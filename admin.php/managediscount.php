<?php
// Kết nối đến cơ sở dữ liệu
require '../connect.php';

// Xử lý xóa ưu đãi nếu có yêu cầu
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $discount_id = $_GET['delete'];
    try {
        $delete_stmt = $pdo->prepare("DELETE FROM discount_products WHERE product_id = :discount_id");
        $delete_stmt->bindParam(':discount_id', $discount_id);
        $delete_stmt->execute();
        
        // Thông báo xóa thành công
        echo "<script>alert('Xóa ưu đãi thành công!');</script>";
        // Chuyển hướng để tránh việc xóa lại khi refresh trang
        echo "<script>window.location.href = 'managediscount.php';</script>";
        exit;
    } catch(PDOException $e) {
        echo "<script>alert('Lỗi khi xóa ưu đãi: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Ưu Đãi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: url('../post/image/br.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            width: 100%;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            margin: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        .button-container {
            display: flex;
            margin-bottom: 20px;
        }
        
        .add-button {
            display: inline-block;
            background-color: #27ae60;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-left: 10px;
            transition: background-color 0.3s;
        }
        
        .add-button:hover {
            background-color: #2ecc71;
        }
        
        .back-button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .back-button:hover {
            background-color: #2980b9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .action-buttons a {
            display: inline-block;
            padding: 6px 12px;
            text-decoration: none;
            color: white;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .edit-button {
            background-color: #f39c12;
            margin-bottom: 10px;
        }
        
        .edit-button:hover {
            background-color: #e67e22;
        }
        
        .delete-button {
            background-color: #e74c3c;
        }
        
        .delete-button:hover {
            background-color: #c0392b;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        .active {
            background-color: #dff0d8;
            color: #3c763d;
        }
        
        .expired {
            background-color: #f2dede;
            color: #a94442;
        }
        
        .upcoming {
            background-color: #d9edf7;
            color: #31708f;
        }
        
        .search-container {
            margin-bottom: 20px;
            display: flex;
        }
        
        .search-container input[type=text] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            outline: none;
        }
        
        .search-container button {
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .search-container button:hover {
            background-color: #2980b9;
        }
        
        .no-discounts {
            text-align: center;
            padding: 20px;
            color: #666;
            background-color: white;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <h1>Quản Lý Ưu Đãi</h1>
            
            <div class="button-container">
                <a href="index.php" class="back-button">← Quay lại Dashboard</a>
                <a href="adddiscount.php" class="add-button">+ Thêm Ưu Đãi Mới</a>
            </div>
        
        <div class="search-container">
            <form action="" method="GET" style="display: flex; width: 100%;">
                <input type="text" name="search" placeholder="Tìm kiếm theo tên sản phẩm..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Tìm Kiếm</button>
            </form>
        </div>
        
        <?php
        try {
            // Chuẩn bị câu truy vấn SQL
            $sql = "SELECT * FROM discount_products";
            $params = [];
            
            // Thêm điều kiện tìm kiếm nếu có
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = '%' . $_GET['search'] . '%';
                $sql .= " WHERE product_name LIKE :search";
                $params[':search'] = $search;
            }
            
            // Thêm sắp xếp
            $sql .= " ORDER BY start_date DESC";
            
            $stmt = $pdo->prepare($sql);
            
            // Bind các tham số nếu có
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($discounts) > 0) {
                echo '<table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Giảm Giá (%)</th>
                            <th>Giá Sau Giảm</th>
                            <th>Còn Lại</th>
                            <th>Ngày Bắt Đầu</th>
                            <th>Ngày Kết Thúc</th>
                            <th>Trạng Thái</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>';
                
                $current_date = date('Y-m-d H:i:s');
                
                foreach ($discounts as $discount) {
                    // Xác định trạng thái của ưu đãi
                    $status = '';
                    $status_class = '';
                    
                    if ($current_date < $discount['start_date']) {
                        $status = 'Sắp diễn ra';
                        $status_class = 'upcoming';
                    } elseif ($current_date > $discount['end_date']) {
                        $status = 'Đã hết hạn';
                        $status_class = 'expired';
                    } else {
                        $status = 'Đang diễn ra';
                        $status_class = 'active';
                    }
                    
                    echo '<tr>
                        <td>' . htmlspecialchars($discount['product_id']) . '</td>
                        <td>' . htmlspecialchars($discount['product_name']) . '</td>
                        <td>' . htmlspecialchars($discount['discount_percent']) . '%</td>
                        <td>' . number_format($discount['discounted_price'], 0, ',', '.') . ' VNĐ</td>
                        <td>' . htmlspecialchars($discount['remaining']) . '</td>
                        <td>' . date('d/m/Y H:i', strtotime($discount['start_date'])) . '</td>
                        <td>' . date('d/m/Y H:i', strtotime($discount['end_date'])) . '</td>
                        <td><span class="status ' . $status_class . '">' . $status . '</span></td>
                        <td class="action-buttons">
                            <a href="fixdiscount.php?id=' . $discount['product_id'] . '" class="edit-button">Sửa</a>
                            <a href="javascript:void(0);" onclick="confirmDelete(' . $discount['product_id'] . ')" class="delete-button">Xóa</a>
                        </td>
                    </tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<div class="no-discounts">Không tìm thấy ưu đãi nào.</div>';
            }
        } catch(PDOException $e) {
            echo '<div class="no-discounts">Lỗi khi truy vấn dữ liệu: ' . $e->getMessage() . '</div>';
        }
        ?>
        </div>
    </div>
    
    <script>
        function confirmDelete(id) {
            if (confirm('Bạn có chắc chắn muốn xóa ưu đãi này không?')) {
                window.location.href = 'managediscount.php?delete=' + id;
            }
        }
    </script>
</body>
</html>