<?php
// checkout_vnpay.php

ini_set('display_errors',1);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Ho_Chi_Minh');

session_start();
require __DIR__ . '/db.php';
$config = require __DIR__ . '/vnpay_config.php';

// 1) Load order
if (empty($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    die('Access denied');
}
$orderId = (int)$_GET['order_id'];
$stmt    = $pdo->prepare("SELECT total FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) die('Order not found');

// 2) Thời gian
$createDate = date('YmdHis');
$expireDate = date('YmdHis', strtotime('+15 minutes'));

// 3) Các trường bắt buộc (không include SecureHashType/Hash)
$data = [
  'vnp_Version'    => '2.1.0',
  'vnp_Command'    => 'pay',
  'vnp_TmnCode'    => $config['vnp_TmnCode'],
  'vnp_Amount'     => $order['total'] * 100,
  'vnp_CurrCode'   => 'VND',
  'vnp_TxnRef'     => $orderId,
  'vnp_OrderInfo'  => "Thanh toán đơn #{$orderId}",
  'vnp_OrderType'  => 'other',
  'vnp_Locale'     => 'vn',
  'vnp_IpAddr'     => $_SERVER['REMOTE_ADDR'],
  'vnp_CreateDate' => $createDate,
  'vnp_ExpireDate' => $expireDate,
  'vnp_ReturnUrl'  => $config['vnp_ReturnUrl'],
];

// 4) Sinh secure hash chỉ với $data
ksort($data);
$hashData = '';
$i = 0;
foreach ($data as $key => $val) {
    if ($i == 1) {
        $hashData .= '&' . urlencode($key) . '=' . urlencode($val);
    } else {
        $hashData .= urlencode($key) . '=' . urlencode($val);
        $i = 1;
    }
}
$secureHash = hash_hmac('sha512', $hashData, $config['vnp_HashSecret']);

// 5) Xây query string, giờ mới thêm HashType và Hash
$query = [];
foreach ($data as $key => $val) {
    $query[] = urlencode($key) . '=' . urlencode($val);
}
$query[] = 'vnp_SecureHashType=SHA512';
$query[] = 'vnp_SecureHash=' . $secureHash;

// 6) Redirect
header('Location:' . $config['vnp_Url'] . '?' . implode('&', $query));
exit;
