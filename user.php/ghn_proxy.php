<?php
header('Content-Type: application/json');
$token = '68d3bff1-2a29-11f0-a13b-aac0b882ff8a';
$type = $_GET['type'] ?? '';
if ($type === 'province') {
    $url = 'https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/province';
    $headers = ["Token: $token"];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);
    echo $result;
    exit;
}
if ($type === 'district' && isset($_POST['province_id'])) {
    $url = 'https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/district';
    $headers = ["Token: $token", "Content-Type: application/json"];
    $data = json_encode(['province_id' => (int)$_POST['province_id']]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    curl_close($ch);
    echo $result;
    exit;
}
if ($type === 'ward' && isset($_POST['district_id'])) {
    $url = 'https://dev-online-gateway.ghn.vn/shiip/public-api/master-data/ward';
    $headers = ["Token: $token", "Content-Type: application/json"];
    $data = json_encode(['district_id' => (int)$_POST['district_id']]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    curl_close($ch);
    echo $result;
    exit;
}
echo json_encode(['error' => 'Invalid request']);