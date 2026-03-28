<?php
$token = '68d3bff1-2a29-11f0-a13b-aac0b882ff8a';

// Lấy danh sách tỉnh/thành phố
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/province",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Token: $token"
    ],
]);
$response = curl_exec($curl);
$provinces = json_decode($response, true);
curl_close($curl);

// Lấy danh sách quận/huyện
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/district",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Token: 68d3bff1-2a29-11f0-a13b-aac0b882ff8a"
    ],
]);
$response = curl_exec($curl);
$districts = json_decode($response, true);
curl_close($curl);

// Lấy thông tin shop
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shop/all",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Token: $token"
    ],
]);
$response = curl_exec($curl);
$shops = json_decode($response, true);
curl_close($curl);
?>

<!DOCTYPE html>
<html>
<head>
    <title>GHN Information</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin-bottom: 30px; }
        h2 { color: #2c3e50; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f6fa; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="section">
        <h2>Thông tin Shop</h2>
        <?php if (isset($shops['data'])): ?>
            <table>
                <tr>
                    <th>Shop ID</th>
                    <th>Tên Shop</th>
                    <th>Địa chỉ</th>
                    <th>Quận/Huyện ID</th>
                </tr>
                <?php foreach ($shops['data'] as $shop): ?>
                <tr>
                    <td><?= $shop['shop_id'] ?></td>
                    <td><?= $shop['name'] ?></td>
                    <td><?= $shop['address'] ?></td>
                    <td><?= $shop['district_id'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Không lấy được thông tin shop</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Danh sách Tỉnh/Thành phố</h2>
        <?php if (isset($provinces['data'])): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                </tr>
                <?php foreach ($provinces['data'] as $province): ?>
                <tr>
                    <td><?= $province['ProvinceID'] ?></td>
                    <td><?= $province['ProvinceName'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Không lấy được danh sách tỉnh/thành phố</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Danh sách Quận/Huyện</h2>
        <?php if (isset($districts['data'])): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Tỉnh/Thành ID</th>
                </tr>
                <?php foreach ($districts['data'] as $district): ?>
                <tr>
                    <td><?= $district['DistrictID'] ?></td>
                    <td><?= $district['DistrictName'] ?></td>
                    <td><?= $district['ProvinceID'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Không lấy được danh sách quận/huyện</p>
        <?php endif; ?>
    </div>
</body>
</html> 