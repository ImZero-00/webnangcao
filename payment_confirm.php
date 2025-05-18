<?php
// payment_confirm.php
// Trang xác nhận đơn hàng và chuyển tiếp sang VNPAY

// Bật debug (comment lại khi vào production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Ho_Chi_Minh');

// 1. Khởi session và kết nối DB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/db.php';
include __DIR__ . '/templates/header.php';

// 2. Kiểm tra user đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 3. Lấy order_id từ query string
if (empty($_GET['order_id'])) {
    echo '<p class="alert alert-danger">Order ID missing.</p>';
    include __DIR__ . '/templates/footer.php';
    exit;
}
$orderId = (int) $_GET['order_id'];

// 4. Lấy thông tin đơn hàng
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    echo '<p class="alert alert-danger">Order not found or access denied.</p>';
    include __DIR__ . '/templates/footer.php';
    exit;
}

// 5. Lấy order_items
$stmtItems = $pdo->prepare(
    "SELECT product_id, quantity, price FROM order_items WHERE order_id = ?"
);
$stmtItems->execute([$orderId]);
$orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// 6. Lấy products từ API
$apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/products_api.php';
$json = @file_get_contents($apiUrl);
$allProducts = $json ? json_decode($json, true) : [];

// 7. Map sản phẩm theo ID
$productMap = [];
foreach ($allProducts as $prod) {
    $productMap[$prod['id']] = $prod;
}

// 8. Kết hợp thông tin
$items = [];
foreach ($orderItems as $it) {
    $pid = $it['product_id'];
    if (isset($productMap[$pid])) {
        $items[] = [
            'product_id' => $pid,
            'quantity'   => $it['quantity'],
            'price'      => $it['price'],
            'title'      => $productMap[$pid]['title'],
            'image'      => $productMap[$pid]['image'],
        ];
    }
}
?>
<main class="container" style="margin: 40px auto; max-width:800px;">
  <h2>Chi tiết đơn hàng #<?= htmlspecialchars($order['id']) ?></h2>
  <p><strong>Thời gian tạo:</strong> <?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></p>
  <table class="table" style="width:100%; border-collapse:collapse; margin-top:20px;">
    <thead>
      <tr>
        <th>Ảnh</th>
        <th>Sản phẩm</th>
        <th>Số lượng</th>
        <th>Đơn giá</th>
        <th>Thành tiền</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $it): ?>
      <tr>
        <td><img src="<?= htmlspecialchars($it['image']) ?>" alt="" width="60"></td>
        <td><?= htmlspecialchars($it['title']) ?></td>
        <td><?= htmlspecialchars($it['quantity']) ?></td>
        <td><?= number_format($it['price'], 0, ',', '.') ?> đ</td>
        <td><?= number_format($it['price'] * $it['quantity'], 0, ',', '.') ?> đ</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p style="margin-top:20px;"><strong>Tổng cộng:</strong> <?= number_format($order['total'], 0, ',', '.') ?> đ</p>
  <form action="checkout_vnpay.php" method="get" style="text-align:center; margin-top:20px;">
    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
    <button type="submit" class="btn btn-primary">Thanh toán qua VNPAY</button>
  </form>
</main>
<?php include __DIR__ . '/templates/footer.php'; ?>
