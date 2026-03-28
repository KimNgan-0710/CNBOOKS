<nav class="sidebar">
    <ul>
        <li><a href="main.php">📊 Tổng Quan</a></li>
        <li class="dropdown">
            <a href="products.php">👕 Sản Phẩm</a>
           <!-- <ul class="dropdown-menu">
                 <li><a href="products.php">📦 Xem Sản Phẩm</a></li>
                <li><a href="add_product.php">➕ Thêm Sản Phẩm</a></li>
                <li><a href="edit_product.php">✏️ Sửa Sản Phẩm</a></li>
                <li><a href="delete_product.php">🗑️ Xóa Sản Phẩm</a></li>
            </ul> -->
        </li>
        <li class="dropdown">
            <a href="managediscount.php">🎁 Ưu Đãi</a>
            <!-- <ul class="dropdown-menu">
                <li><a href="managediscount.php">📜 Quản Lý Ưu Đãi</a></li>
                <li><a href="adddiscount.php">➕ Thêm Ưu Đãi</a></li>
                <li><a href="edit_deal.php">✏️ Sửa Ưu Đãi</a></li>
                <li><a href="deletediscount.php">🗑️ Xóa Ưu Đãi</a></li>
            </ul> -->
        </li>
        <li class="dropdown">
            <a href="posts_management.php">📝 Bài Đăng</a>
            <!-- <ul class="dropdown-menu">
                <li><a href="posts_management.php">📄 Quản Lý Bài Đăng</a></li>
                <li><a href="add_post.php">➕ Thêm Bài Đăng</a></li>
                <li><a href="../post/post.php">🌐 Xem Trang Bài Đăng</a></li>
            </ul> -->
        </li>
        <li class="dropdown">
            <a href="#">📈 Phân Tích</a>
            <ul class="dropdown-menu">
                <li><a href="analytics_users.php">👤 Người Đăng Nhập</a></li>
                <li><a href="analytics_sales.php">📚 Sách Được Bán</a></li>
                <li><a href="analytics_interactions.php">💬 Tương Tác</a></li>
            </ul>
        </li>
        <li><a href="../index.php">🏠 Trang chủ</a></li>
        <li><a href="feedback.php">�� Phản hồi</a></li>
        <li><a href="admin_orders.php">📦 Quản Lý Đơn Hàng</a></li>
    </ul>
</nav>

<style>
.sidebar {
    width: 250px;
    background: #f8f9fa;
    padding: 15px;
    border-right: 2px solid #d1d3d4;
    border-radius: 15px;
    box-shadow: 3px 3px 10px rgba(0, 0, 0, 0.1);
    font-family: 'Poppins', sans-serif;
}
.sidebar ul {
    list-style: none;
    padding: 0;
}
.sidebar ul li {
    padding: 12px;
    border-bottom: 1px solid #d1d3d4;
    transition: all 0.3s ease;
    border-radius: 10px;
    margin: 5px 0;
    background: #e3e7ed;
}
.sidebar ul li a {
    text-decoration: none;
    color: #3a3d40;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
}
.sidebar ul li a:hover {
    color: #007bff;
    background: #cfe2ff;
    border-radius: 10px;
    
}
.dropdown-menu {
    display: none;
    padding-left: 20px;
}
.dropdown:hover .dropdown-menu {
    display: block;
}
</style>