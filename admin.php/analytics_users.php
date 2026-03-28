<?php
require '../connect.php';

// Thiết lập tiêu đề trang
$page_title = 'Phân Tích Người Dùng';

// Lấy tổng số người dùng
try {
    $totalUsersStmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $totalUsersStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Lấy số người dùng theo vai trò
    $roleStmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $roleData = $roleStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lấy danh sách người dùng với đầy đủ thông tin
    $usersStmt = $pdo->query("SELECT id, username, fullname, phone, email, role FROM users ORDER BY id DESC");
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// CSS bổ sung cho trang này
$extra_css = '
    .content-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }
    
    .header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    
    .header h1 {
        color: #2c3e50;
        font-size: 24px;
    }
    
    .stats-container {
        display: flex;
        justify-content: space-around;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }
    
    .stat-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        width: 200px;
        text-align: center;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .stat-card h3 {
        margin-top: 0;
        color: #7f8c8d;
        font-size: 16px;
    }
    
    .stat-card .number {
        font-size: 32px;
        font-weight: bold;
        color: #3498db;
    }
    
    .users-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .users-table th, .users-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    .users-table th {
        background-color: #f2f2f2;
        color: #333;
        font-weight: bold;
    }
    
    .users-table tr:hover {
        background-color: #f5f5f5;
    }
    
    .role-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .role-admin {
        background-color: #e74c3c;
        color: white;
    }
    
    .role-user {
        background-color: #3498db;
        color: white;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
    }

    .action-buttons a {
        padding: 6px 12px;
        text-decoration: none;
        color: white;
        border-radius: 4px;
        transition: background-color 0.3s;
    }
    
    
    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .btn-edit {
        background-color: #f39c12;
        color: white;
    }
    
    .btn-delete {
        background-color: #e74c3c;
        color: white;
        padding: 6px 12px;
        text-decoration: none;
        color: white;
        border-radius: 4px;
        transition: background-color 0.3s;
        cursor: pointer;
    }
    
    .btn-add-user {
        background-color: #2ecc71;
        color: white;
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        display: inline-block;
    }
    
    .btn-add-user:hover {
        background-color: #27ae60;
    }
    
    .search-container {
        margin-bottom: 20px;
    }
    
    .search-input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }
';

// JavaScript bổ sung cho trang này
$extra_js = '
    function searchUsers() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toUpperCase();
        const table = document.getElementById("usersTable");
        const tr = table.getElementsByTagName("tr");
        
        for (let i = 1; i < tr.length; i++) {
            const tdUsername = tr[i].getElementsByTagName("td")[1];
            const tdFullname = tr[i].getElementsByTagName("td")[2];
            const tdEmail = tr[i].getElementsByTagName("td")[4];
            
            if (tdUsername || tdFullname || tdEmail) {
                const usernameValue = tdUsername.textContent || tdUsername.innerText;
                const fullnameValue = tdFullname.textContent || tdFullname.innerText;
                const emailValue = tdEmail.textContent || tdEmail.innerText;
                
                if (usernameValue.toUpperCase().indexOf(filter) > -1 || 
                    fullnameValue.toUpperCase().indexOf(filter) > -1 || 
                    emailValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    
    function confirmDelete(id, username) {
        if (confirm("Bạn có chắc chắn muốn xóa người dùng " + username + "?")) {
            window.location.href = "delete_user.php?id=" + id;
        }
    }
';

// Bắt đầu nội dung trang
ob_start();
?>

<div class="content-wrapper">
    <div class="header">
        <h1>Phân Tích Người Dùng</h1>
        <div style="text-align: right; margin-top: 10px;">
            <a href="add_user.php" class="btn btn-add-user">Thêm Người Dùng Mới</a>
        </div>
    </div>
    
    <div class="stats-container">
        <div class="stat-card">
            <h3>Tổng Người Dùng</h3>
            <div class="number"><?= $totalUsers ?></div>
        </div>
        
        <?php foreach ($roleData as $role): ?>
            <div class="stat-card">
                <h3><?= $role['role'] == 1 ? 'Quản Trị Viên' : 'Người Dùng Thường' ?></h3>
                <div class="number"><?= $role['count'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="search-container">
        <input type="text" id="searchInput" class="search-input" placeholder="Tìm kiếm theo tên đăng nhập, họ tên hoặc email..." onkeyup="searchUsers()">
    </div>
    
    <table class="users-table" id="usersTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Họ tên</th>
                <th>Số điện thoại</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['fullname']) ?></td>
                    <td><?= htmlspecialchars($user['phone']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="role-badge <?= $user['role'] == 1 ? 'role-admin' : 'role-user' ?>">
                            <?= $user['role'] == 1 ? 'Quản trị viên' : 'Người dùng' ?>
                        </span>
                    </td>
                    <td class="action-buttons">
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-edit">Sửa</a>
                        <button onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')" class="btn btn-sm btn-delete">Xóa</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
// Kết thúc nội dung trang
$page_content = ob_get_clean();

// Nhúng layout
include 'admin_layout.php';
?>