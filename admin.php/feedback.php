<?php
require 'connect.php';

// Xử lý xóa liên hệ
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->execute([$id]);
}

// Xử lý xuất Excel
if (isset($_POST['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="contacts_export.xls"');
    $stmt = $pdo->query("SELECT * FROM contacts ORDER BY id DESC");
    $contacts = $stmt->fetchAll();
    echo "ID\tTên\tSố điện thoại\tEmail\tChủ đề\tNội dung\n";
    foreach ($contacts as $contact) {
        echo $contact['id'] . "\t";
        echo $contact['name'] . "\t";
        echo $contact['phone'] . "\t";
        echo $contact['email'] . "\t";
        echo $contact['subject'] . "\t";
        echo $contact['message'] . "\n";
    }
    exit();
}

// Xử lý cập nhật trạng thái
if (isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE contacts SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

// Lấy danh sách liên hệ
$stmt = $pdo->query("SELECT * FROM contacts ORDER BY id DESC");
$contacts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Liên Hệ</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif;}
        body {background-color: #f5f5f5; padding: 20px;}
        .container {max-width: 1200px; margin: 0 auto;}
        h1 {color: #333; margin-bottom: 20px; text-align: center;}
        .actions {margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;}
        .search-box {display: flex; gap: 10px;}
        .search-box input {padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 300px;}
        .search-box button {padding: 8px 15px; background: #1e90ff; color: white; border: none; border-radius: 4px; cursor: pointer;}
        .export-btn {padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;}
        table {width: 100%; border-collapse: collapse; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1);}
        th, td {padding: 12px; text-align: left; border-bottom: 1px solid #ddd;}
        th {background: #f8f9fa; font-weight: 600;}
        tr:hover {background: #f8f9fa;}
        .delete-btn {padding: 4px 8px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;}
        .status-pending {background-color: #ffe5e5 !important;}
        .status-processing {background-color: #fff3cd !important;}
        .status-done {background-color: #e6ffe5 !important;}
    </style>
</head>
<body>
    <div class="container">
        <h1>Quản Lý Liên Hệ</h1>
        <div class="actions">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Tìm kiếm...">
                <button onclick="searchContact()">Tìm kiếm</button>
            </div>
            <form method="POST" style="display: inline;">
                <button type="submit" name="export_excel" class="export-btn">Xuất Excel</button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Số điện thoại</th>
                    <th>Email</th>
                    <th>Chủ đề</th>
                    <th>Nội dung</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $contact): ?>
                <tr>
                    <td><?php echo $contact['id']; ?></td>
                    <td><?php echo htmlspecialchars($contact['name']); ?></td>
                    <td><?php echo htmlspecialchars($contact['phone']); ?></td>
                    <td><?php echo htmlspecialchars($contact['email']); ?></td>
                    <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                    <td><?php echo htmlspecialchars($contact['message']); ?></td>
                    <?php
                    $statusClass = '';
                    switch ($contact['status']) {
                        case 'Chưa xử lý':
                            $statusClass = 'status-pending';
                            break;
                        case 'Đang xử lý':
                            $statusClass = 'status-processing';
                            break;
                        case 'Đã xử lý':
                            $statusClass = 'status-done';
                            break;
                    }
                    ?>
                    <td class="<?php echo $statusClass; ?>">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="Chưa xử lý" <?php if($contact['status']==='Chưa xử lý') echo 'selected'; ?>>Chưa xử lý</option>
                                <option value="Đang xử lý" <?php if($contact['status']==='Đang xử lý') echo 'selected'; ?>>Đang xử lý</option>
                                <option value="Đã xử lý" <?php if($contact['status']==='Đã xử lý') echo 'selected'; ?>>Đã xử lý</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
    function searchContact() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchText) ? '' : 'none';
        });
    }
    </script>
</body>
</html> 