<?php
session_start();
require 'connect.php';
// Kiểm tra quyền admin
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit;
}
// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
    $orderId = $_POST['order_id'];
    $action = $_POST['action'];
    $shippingProvider = $_POST['shipping_provider'] ?? null;
    $trackingCode = $_POST['tracking_code'] ?? null;
    $statusMap = [
        'confirm' => 'confirmed',
        'pack' => 'packing',
        'ship' => 'shipping',
        'deliver' => 'delivered',
        'cancel' => 'cancelled',
    ];
    if (isset($statusMap[$action])) {
        $update = $pdo->prepare("UPDATE orders SET shipping_status = :status, shipping_provider = :provider, tracking_code = :code WHERE id = :id");
        $update->bindParam(':status', $statusMap[$action]);
        $update->bindParam(':provider', $shippingProvider);
        $update->bindParam(':code', $trackingCode);
        $update->bindParam(':id', $orderId);
        $update->execute();
    }
    header('Location: admin_orders.php');
    exit;
}
// Lấy danh sách đơn hàng
$orders = $pdo->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC")->fetchAll(PDO::FETCH_ASSOC);
// Lấy danh sách đơn vị vận chuyển mẫu
$shippingProviders = ['Giao Hàng Nhanh', 'Viettel Post', 'J&T Express', 'VNPost', 'Shopee Express'];
include '../header.php';
?>
<div class="container" style="margin-top: 60px; margin-bottom: 40px; max-width: 1100px;">
    <h2 style="color: #1e90ff; text-align: center; margin-bottom: 30px;">Quản Lý Đơn Hàng</h2>
    <div class="admin-order-table">
        <table>
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Đơn vị VC</th>
                    <th>Mã vận đơn</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= htmlspecialchars($order['order_id']) ?></td>
                    <td><?= htmlspecialchars($order['username'] ?? 'Khách vãng lai') ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                    <td><?= number_format($order['total_price'], 0, ',', '.') ?>₫</td>
                    <td>
                        <?php
                        $statusLabel = [
                            'pending' => 'Chờ xác nhận',
                            'confirmed' => 'Đã xác nhận',
                            'packing' => 'Đang đóng gói',
                            'shipping' => 'Đang giao hàng',
                            'delivered' => 'Đã giao hàng',
                            'cancelled' => 'Đã hủy',
                        ];
                        $statusColor = [
                            'pending' => '#f39c12',
                            'confirmed' => '#2980b9',
                            'packing' => '#8e44ad',
                            'shipping' => '#e67e22',
                            'delivered' => '#27ae60',
                            'cancelled' => '#e74c3c',
                        ];
                        $shippingStatus = $order['shipping_status'] ?? 'pending';
                        echo '<span style="color:' . $statusColor[$shippingStatus] . '; font-weight:bold;">' . $statusLabel[$shippingStatus] . '</span>';
                        ?>
                    </td>
                    <td><?= htmlspecialchars($order['shipping_provider'] ?? '') ?></td>
                    <td><?= htmlspecialchars($order['tracking_code'] ?? '') ?></td>
                    <td>
                        <form method="POST" class="order-action-form">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="action" class="action-select" onchange="this.form.submit()">
                                <option value="">Chọn thao tác</option>
                                <?php if ($shippingStatus == 'pending'): ?><option value="confirm">Xác nhận</option><?php endif; ?>
                                <?php if ($shippingStatus == 'confirmed'): ?><option value="pack">Đóng gói</option><?php endif; ?>
                                <?php if ($shippingStatus == 'packing'): ?><option value="ship">Giao hàng</option><?php endif; ?>
                                <?php if ($shippingStatus == 'shipping'): ?><option value="deliver">Đã giao hàng</option><?php endif; ?>
                                <?php if ($shippingStatus != 'delivered' && $shippingStatus != 'cancelled'): ?><option value="cancel">Hủy đơn</option><?php endif; ?>
                            </select>
                            <select name="shipping_provider" class="provider-select">
                                <option value="">Đơn vị VC</option>
                                <?php foreach ($shippingProviders as $provider): ?>
                                    <option value="<?= htmlspecialchars($provider) ?>" <?= ($order['shipping_provider'] == $provider ? 'selected' : '') ?>><?= htmlspecialchars($provider) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="tracking_code" value="<?= htmlspecialchars($order['tracking_code'] ?? '') ?>" placeholder="Mã vận đơn" class="tracking-input">
                            <a href="../order_confirmation.php?id=<?= urlencode($order['order_id']) ?>" class="btn-view-detail" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                            <button type="button" class="btn-ghn" style="display: none;">Tạo vận đơn GHN</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<style>
.order-action-form {
    display: flex;
    align-items: center;
    gap: 6px;
}
.action-select, .provider-select, .tracking-input {
    padding: 4px 8px;
    border-radius: 5px;
    border: 1px solid #d1d3d4;
    font-size: 14px;
    min-width: 90px;
}
.tracking-input {
    width: 100px;
}
.btn-view-detail {
    background: #1e90ff;
    color: #fff;
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 15px;
    margin-left: 4px;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.btn-view-detail:hover { background: #155fa0; }
.admin-order-table table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.admin-order-table th, .admin-order-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #f0f0f0;
    text-align: center;
    vertical-align: middle;
}
.admin-order-table th {
    background: #f0f8ff;
    color: #1e90ff;
    font-weight: bold;
}
.btn-ghn {
    background: #e74c3c;
    color: #fff;
    padding: 5px 10px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
}
.btn-ghn:hover {
    background: #c0392b;
}
</style>

<script>
document.querySelectorAll('.provider-select').forEach(function(select) {
    select.addEventListener('change', function() {
        const form = this.closest('form');
        const btnGhn = form.querySelector('.btn-ghn');
        
        if (this.value === 'Giao Hàng Nhanh') {
            btnGhn.style.display = 'inline-block';
        } else {
            btnGhn.style.display = 'none';
        }
    });

    // Trigger change event nếu đã chọn GHN
    if (select.value === 'Giao Hàng Nhanh') {
        select.dispatchEvent(new Event('change'));
    }
});

document.querySelectorAll('.btn-ghn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const form = this.closest('form');
        const orderId = form.querySelector('input[name="order_id"]').value;
        
        // Lấy thông tin đơn hàng từ database
        fetch('../get_order_info.php?id=' + orderId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Hiển thị form nhập thông tin giao hàng
                const toName = prompt('Tên người nhận:', data.shipping_name);
                if (!toName) return;

                const toPhone = prompt('Số điện thoại:', data.shipping_phone);
                if (!toPhone) return;

                const toAddress = prompt('Địa chỉ:', data.shipping_address);
                if (!toAddress) return;

                const toWardCode = prompt('Mã phường (GHN):');
                if (!toWardCode) return;

                const toDistrictId = prompt('ID quận (GHN):');
                if (!toDistrictId) return;

                const codAmount = prompt('Tiền thu hộ:', data.total_price);
                if (!codAmount) return;

                // Gọi API tạo vận đơn GHN
                fetch('../ghn_api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({
                        order_id: orderId,
                        to_name: toName,
                        to_phone: toPhone,
                        to_address: toAddress,
                        to_ward_code: toWardCode,
                        to_district_id: toDistrictId,
                        cod_amount: codAmount,
                        items: JSON.stringify(data.items)
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.data && result.data.order_code) {
                        form.querySelector('.tracking-input').value = result.data.order_code;
                        alert('Tạo vận đơn GHN thành công!');
                    } else {
                        alert('Lỗi tạo vận đơn GHN: ' + (result.message || ''));
                    }
                })
                .catch(error => {
                    alert('Lỗi: ' + error.message);
                });
            });
    });
});
</script>
<?php include '../footer.php'; ?> 