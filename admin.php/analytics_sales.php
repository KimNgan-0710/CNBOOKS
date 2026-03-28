<?php
require '../connect.php';

// Thiết lập tiêu đề trang
$page_title = 'Phân Tích Doanh Số';

// Lấy thông tin thống kê
try {
    // Tổng doanh thu
    $totalRevenueStmt = $pdo->query("SELECT SUM(total_price) as total FROM orders WHERE shipping_status = 'delivered'");
    $totalRevenue = $totalRevenueStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Tổng số đơn hàng
    $totalOrdersStmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $totalOrdersStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Đơn hàng theo trạng thái
    $orderStatusStmt = $pdo->query("SELECT shipping_status, COUNT(*) as count FROM orders GROUP BY shipping_status");
    $orderStatus = $orderStatusStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sản phẩm bán chạy nhất
    $topProductsStmt = $pdo->query("
        SELECT p.product_id, p.product_name, COUNT(od.product_id) as order_count, SUM(od.quantity) as total_quantity
        FROM order_details od
        JOIN products p ON od.product_id = p.product_id
        JOIN orders o ON od.order_id = o.id
        WHERE o.shipping_status = 'delivered'
        GROUP BY od.product_id
        ORDER BY total_quantity DESC
        LIMIT 5
    ");
    $topProducts = $topProductsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Doanh thu theo tháng trong năm hiện tại
    $currentYear = date('Y');
    $monthlySalesStmt = $pdo->prepare("
        SELECT SUM(total_price) as revenue
        FROM orders
        WHERE YEAR(order_date) = :year AND MONTH(order_date) = 5 AND shipping_status = 'delivered'
    ");
    $monthlySalesStmt->bindParam(':year', $currentYear);
    $monthlySalesStmt->execute();
    $maySales = $monthlySalesStmt->fetch(PDO::FETCH_ASSOC);
    
    // Đơn hàng gần đây
    $recentOrdersStmt = $pdo->query("
        SELECT o.id, o.order_date, o.total_price, o.shipping_status, u.username
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.order_date DESC
        LIMIT 10
    ");
    $recentOrders = $recentOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Thêm các tiêu chí thống kê
    $deliveredOrdersStmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE shipping_status = 'delivered'");
    $deliveredOrders = $deliveredOrdersStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $cancelledOrdersStmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE shipping_status = 'cancelled'");
    $cancelledOrders = $cancelledOrdersStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $processingOrdersStmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE shipping_status = 'processing'");
    $processingOrders = $processingOrdersStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Thêm các trạng thái mới
    $confirmedOrdersStmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE shipping_status = 'confirmed'");
    $confirmedOrders = $confirmedOrdersStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $pendingOrdersStmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE shipping_status = 'pending'");
    $pendingOrders = $pendingOrdersStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $packingOrdersStmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE shipping_status = 'packing'");
    $packingOrders = $packingOrdersStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $shippingOrdersStmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE shipping_status = 'shipping'");
    $shippingOrders = $shippingOrdersStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Chuẩn bị dữ liệu cho biểu đồ chỉ cho tháng 5
$chartLabels = ['Tháng 5'];
$chartData = [(float)($maySales['revenue'] ?? 0)];

$chartLabelsJson = json_encode($chartLabels);
$chartDataJson = json_encode($chartData);

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
        font-size: 24px;
        font-weight: bold;
        color: #3498db;
    }
    
    .stat-card .currency {
        font-size: 14px;
        color: #7f8c8d;
    }
    
    .chart-container {
        margin-bottom: 30px;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .chart-title {
        text-align: center;
        margin-bottom: 20px;
        color: #2c3e50;
        font-size: 18px;
    }
    
    .data-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .data-section {
        flex: 1;
        min-width: 300px;
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .section-title {
        color: #2c3e50;
        font-size: 18px;
        margin-top: 0;
        margin-bottom: 20px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 10px;
    }
    
    .top-products-table, .recent-orders-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .top-products-table th, .top-products-table td,
    .recent-orders-table th, .recent-orders-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    
    .top-products-table th, .recent-orders-table th {
        background-color: #f2f2f2;
        color: #333;
        font-weight: bold;
    }
    
    .status-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .status-completed {
        background-color: #2ecc71;
        color: white;
    }
    
    .status-pending {
        background-color: #f39c12;
        color: white;
    }
    
    .status-cancelled {
        background-color: #e74c3c;
        color: white;
    }
    
    .status-processing {
        background-color: #3498db;
        color: white;
    }
    
    .status-shipping {
        background-color: #9b59b6;
        color: white;
    }
    
    .charts-flex {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }
    
    .chart-container {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin-bottom: 0;
        min-width: 320px;
        max-width: 400px;
        flex: 1 1 350px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    @media (max-width: 900px) {
        .charts-flex {
            flex-direction: column;
            align-items: center;
        }
        .chart-container {
            max-width: 100%;
            min-width: 0;
        }
    }
';

// JavaScript bổ sung cho trang này (Chart.js)
$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Biểu đồ doanh thu theo tháng
    const ctx = document.getElementById("monthlySalesChart").getContext("2d");
    const monthlySalesChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: ' . $chartLabelsJson . ',
            datasets: [{
                label: "Doanh thu (VNĐ)",
                data: ' . $chartDataJson . ',
                backgroundColor: "rgba(52, 152, 219, 0.5)",
                borderColor: "rgba(52, 152, 219, 1)",
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString("vi-VN") + " VNĐ";
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ": " + context.raw.toLocaleString("vi-VN") + " VNĐ";
                        }
                    }
                }
            }
        }
    });
    
    // Biểu đồ trạng thái đơn hàng
    const statusLabels = [
        "Chờ xử lý", "Đã xác nhận", "Đang đóng gói", "Đang giao", "Giao thành công", "Đang xử lý", "Đã hủy"
    ];
    const statusData = [
        ' . $pendingOrders . ',
        ' . $confirmedOrders . ',
        ' . $packingOrders . ',
        ' . $shippingOrders . ',
        ' . $deliveredOrders . ',
        ' . $processingOrders . ',
        ' . $cancelledOrders . '
    ];
    const backgroundColors = [
        "#f39c12", "#2980b9", "#8e44ad", "#9b59b6", "#2ecc71", "#3498db", "#e74c3c"
    ];
    const ctxPie = document.getElementById("orderStatusChart").getContext("2d");
    const orderStatusChart = new Chart(ctxPie, {
        type: "pie",
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: backgroundColors,
                borderColor: "white",
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "right"
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || "";
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return label + ": " + value + " đơn hàng (" + percentage + "%)";
                        }
                    }
                }
            }
        }
    });
});
</script>
';

// Bắt đầu nội dung trang
ob_start();
?>

<div class="content-wrapper">
    <div class="header">
        <h1>Phân Tích Doanh Số Bán Hàng</h1>
    </div>
    
    <div class="stats-container">
        <div class="stat-card">
            <h3>Tổng Doanh Thu</h3>
            <div class="number"><?= number_format($totalRevenue, 0, ',', '.') ?></div>
            <div class="currency">VNĐ</div>
        </div>
        
        <div class="stat-card">
            <h3>Tổng Đơn Hàng</h3>
            <div class="number"><?= $totalOrders ?></div>
            <div class="currency">đơn hàng</div>
        </div>
        
        <div class="stat-card">
            <h3>Đơn Chờ Xử Lý</h3>
            <div class="number"><?= $pendingOrders ?></div>
            <div class="currency">đơn hàng</div>
        </div>
        
        <div class="stat-card">
            <h3>Đơn Đã Xác Nhận</h3>
            <div class="number"><?= $confirmedOrders ?></div>
            <div class="currency">đơn hàng</div>
        </div>
        
        <div class="stat-card">
            <h3>Đơn Đang Đóng Gói</h3>
            <div class="number"><?= $packingOrders ?></div>
            <div class="currency">đơn hàng</div>
        </div>
        
        <div class="stat-card">
            <h3>Đơn Đang Giao</h3>
            <div class="number"><?= $shippingOrders ?></div>
            <div class="currency">đơn hàng</div>
        </div>
        
        <div class="stat-card">
            <h3>Đơn Giao Thành Công</h3>
            <div class="number"><?= $deliveredOrders ?></div>
            <div class="currency">đơn hàng</div>
        </div>
        
        <div class="stat-card">
            <h3>Đơn Đang Xử Lý</h3>
            <div class="number"><?= $processingOrders ?></div>
            <div class="currency">đơn hàng</div>
        </div>
        
        <div class="stat-card">
            <h3>Đơn Bị Hủy</h3>
            <div class="number"><?= $cancelledOrders ?></div>
            <div class="currency">đơn hàng</div>
        </div>
    </div>
    
    <div class="charts-flex">
        <div class="chart-container">
            <div class="chart-title">Doanh Thu Tháng 5 (<?= $currentYear ?>)</div>
            <canvas id="monthlySalesChart" width="350" height="220"></canvas>
        </div>
        <div class="chart-container">
            <div class="chart-title">Trạng Thái Đơn Hàng</div>
            <canvas id="orderStatusChart" width="350" height="220"></canvas>
        </div>
    </div>
    
    <div class="data-container">
        <div class="data-section">
            <h3 class="section-title">Sản Phẩm Bán Chạy Nhất</h3>
            <table class="top-products-table">
                <thead>
                    <tr>
                        <th>Sản Phẩm</th>
                        <th>Số Lượng Bán</th>
                        <th>Số Đơn Hàng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($topProducts)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">Chưa có dữ liệu</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($topProducts as $product): ?>
                            <tr>
                                <td><a href="../product.php?id=<?= $product['product_id'] ?>"><?= htmlspecialchars($product['product_name']) ?></a></td>
                                <td><?= $product['total_quantity'] ?></td>
                                <td><?= $product['order_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="data-section">
            <h3 class="section-title">Đơn Hàng Gần Đây</h3>
            <table class="recent-orders-table">
                <thead>
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Ngày Đặt</th>
                        <th>Khách Hàng</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Chưa có đơn hàng nào</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><a href="order_details.php?id=<?= $order['id'] ?>">#<?= $order['id'] ?></a></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                                <td><?= htmlspecialchars($order['username'] ?? 'Khách vãng lai') ?></td>
                                <td><?= number_format($order['total_price'], 0, ',', '.') ?> VNĐ</td>
                                <td>
                                    <span class="status-badge status-<?= $order['shipping_status'] ?>">
                                        <?php
                                        switch ($order['shipping_status']) {
                                            case 'completed':
                                                echo 'Hoàn thành';
                                                break;
                                            case 'pending':
                                                echo 'Chờ xử lý';
                                                break;
                                            case 'cancelled':
                                                echo 'Đã hủy';
                                                break;
                                            case 'processing':
                                                echo 'Đang xử lý';
                                                break;
                                            case 'shipping':
                                                echo 'Đang giao hàng';
                                                break;
                                            default:
                                                echo ucfirst($order['shipping_status']);
                                        }
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Kết thúc nội dung trang
$page_content = ob_get_clean();

// Nhúng layout
include 'admin_layout.php';
?>