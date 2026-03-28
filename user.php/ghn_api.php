<?php
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];
    $toName = $_POST['to_name'];
    $toPhone = $_POST['to_phone'];
    $toAddress = $_POST['to_address'];
    $toWardCode = $_POST['to_ward_code'];
    $toDistrictId = $_POST['to_district_id'];
    $codAmount = $_POST['cod_amount'];
    $items = $_POST['items'];
    $token = '68d3bff1-2a29-11f0-a13b-aac0b882ff8a';

    // Lấy thông tin đơn hàng từ database
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['error' => 'Không tìm thấy đơn hàng']);
        exit;
    }

    // Lấy chi tiết sản phẩm trong đơn hàng
    $stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = ?");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format items cho GHN
    $ghnItems = [];
    foreach ($orderItems as $item) {
        $ghnItems[] = [
            'name' => $item['name'],
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ];
    }

    $data = [
        "payment_type_id" => 2,
        "note" => "Giao hàng nhanh",
        "required_note" => "KHONGCHOXEMHANG",
        "to_name" => $toName,
        "to_phone" => $toPhone,
        "to_address" => $toAddress,
        "to_ward_code" => $toWardCode,
        "to_district_id" => $toDistrictId,
        "cod_amount" => $codAmount,
        "content" => "Đơn hàng từ CNBooks",
        "weight" => 500,
        "length" => 20,
        "width" => 15,
        "height" => 10,
        "service_id" => 53320,
        "service_type_id" => 2,
        "items" => $ghnItems,
        "shop_id" => 5763482,
        "from_district_id" => 1450
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/create",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Token: $token",
            "ShopId: 5763482"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo json_encode(['error' => $err]);
    } else {
        $result = json_decode($response, true);
        if (isset($result['data']['order_code'])) {
            // Cập nhật mã vận đơn vào database
            $stmt = $pdo->prepare("UPDATE orders SET tracking_code = ? WHERE id = ?");
            $stmt->execute([$result['data']['order_code'], $orderId]);
        }
        echo $response;
    }
    exit;
}
?> 