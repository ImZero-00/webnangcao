<?php
// vnpay_return.php - Xử lý kết quả trả về từ VNPay
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/db.php';

// Lấy cấu hình VNPay
$vnp_config = require_once('vnpay_config.php');

// Hàm kiểm tra chữ ký hash từ VNPay
function validateVnpayReturn($vnp_config) {
    $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
    $inputData = array();
    foreach ($_GET as $key => $value) {
        if (substr($key, 0, 4) == "vnp_") {
            $inputData[$key] = $value;
        }
    }
    unset($inputData['vnp_SecureHash']);
    unset($inputData['vnp_SecureHashType']);
    ksort($inputData);
    $hashData = '';
    $i = 0;
    foreach ($inputData as $key => $val) {
        if ($i == 1) {
            $hashData .= '&' . urlencode($key) . '=' . urlencode($val);
        } else {
            $hashData .= urlencode($key) . '=' . urlencode($val);
            $i = 1;
        }
    }
    $secureHash = hash_hmac('sha512', $hashData, $vnp_config['vnp_HashSecret']);
    return $secureHash === $vnp_SecureHash;
}

// Hàm lấy thông báo lỗi từ mã lỗi VNPay
function getVnpayErrorMessage($responseCode) {
    $errors = [
        '01' => 'Giao dịch đã tồn tại',
        '02' => 'Merchant không hợp lệ',
        '03' => 'Dữ liệu gửi sang không đúng định dạng',
        '04' => 'Khởi tạo GD không thành công do Website đang bị tạm khóa',
        '05' => 'Giao dịch không thành công do: Quý khách nhập sai mật khẩu quá số lần quy định',
        '06' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu',
        '07' => 'Giao dịch bị nghi ngờ gian lận',
        '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ',
        '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần',
        '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán',
        '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa',
        '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu',
        '24' => 'Giao dịch không thành công do: Khách hàng hủy giao dịch',
        '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch',
        '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày',
        '75' => 'Ngân hàng thanh toán đang bảo trì',
        '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán',
        '99' => 'Lỗi không xác định',
    ];
    
    return $errors[$responseCode] ?? 'Lỗi không xác định (mã ' . $responseCode . ')';
}

// Lấy thông tin trả về từ VNPay
$vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
$vnp_TxnRef = $_GET['vnp_TxnRef'] ?? '';
$vnp_Amount = $_GET['vnp_Amount'] ?? 0;
$vnp_TransactionStatus = $_GET['vnp_TransactionStatus'] ?? '';
$vnp_BankCode = $_GET['vnp_BankCode'] ?? '';
$vnp_BankTranNo = $_GET['vnp_BankTranNo'] ?? '';
$vnp_PayDate = $_GET['vnp_PayDate'] ?? '';

// Log thông tin callback
error_log("VNPAY Callback - Order ID: $vnp_TxnRef, Response Code: $vnp_ResponseCode, Amount: $vnp_Amount");

include 'templates/header.php';
?>

<main class="container payment-result">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-5">
                <div class="card-header">
                    <h3 class="text-center">Kết quả thanh toán</h3>
                </div>
                <div class="card-body">
                    <?php
                    // Kiểm tra chữ ký và mã trạng thái trả về
                    if (validateVnpayReturn($vnp_config)) {
                        error_log("VNPAY Callback - Hash validation successful");
                        
                        // Kiểm tra trạng thái hiện tại của đơn hàng
                        $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
                        $stmt->execute([$vnp_TxnRef]);
                        $currentStatus = $stmt->fetchColumn();
                        error_log("VNPAY Callback - Current order status: $currentStatus");
                        
                        if ($vnp_ResponseCode === '00') {
                            error_log("VNPAY Callback - Payment successful");
                            // Thanh toán thành công
                            echo '<div class="alert alert-success">Thanh toán thành công!</div>';
                            
                            // Cập nhật trạng thái đơn hàng trong DB
                            try {
                                $stmt = $pdo->prepare("
                                    UPDATE orders 
                                    SET status = 'PAID', 
                                        vnp_response_code = ?,
                                        vnp_bank_code = ?,
                                        vnp_bank_tran_no = ?,
                                        vnp_pay_date = ?
                                    WHERE id = ? AND status != 'PAID'
                                ");
                                $result = $stmt->execute([
                                    $vnp_ResponseCode,
                                    $vnp_BankCode,
                                    $vnp_BankTranNo,
                                    $vnp_PayDate,
                                    $vnp_TxnRef
                                ]);
                                error_log("VNPAY Callback - Update status result: " . ($result ? "success" : "failed"));
                                
                                // Hiển thị thông tin đơn hàng
                                $orderId = $vnp_TxnRef;
                                $stmt = $pdo->prepare("
                                    SELECT o.*, u.username, u.phone
                                    FROM orders o
                                    JOIN users u ON o.user_id = u.id
                                    WHERE o.id = ?
                                ");
                                $stmt->execute([$orderId]);
                                $order = $stmt->fetch();
                                
                                if ($order) {
                                    ?>
                                    <div class="order-details">
                                        <table class="table">
                                            <tr>
                                                <th>Mã đơn hàng:</th>
                                                <td>#<?php echo $order['id']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Khách hàng:</th>
                                                <td><?php echo $order['username']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Số điện thoại:</th>
                                                <td><?php echo $order['phone']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tổng tiền:</th>
                                                <td><?php echo number_format($order['total'], 0, ',', '.'); ?> đ</td>
                                            </tr>
                                            <tr>
                                                <th>Ngân hàng:</th>
                                                <td><?php echo $vnp_BankCode; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Ngày thanh toán:</th>
                                                <td><?php 
                                                    $date = $vnp_PayDate;
                                                    $year = substr($date, 0, 4);
                                                    $month = substr($date, 4, 2);
                                                    $day = substr($date, 6, 2);
                                                    $hour = substr($date, 8, 2);
                                                    $minute = substr($date, 10, 2);
                                                    $second = substr($date, 12, 2);
                                                    echo $day . '/' . $month . '/' . $year . ' ' . $hour . ':' . $minute . ':' . $second;
                                                ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <?php
                                }
                                
                            } catch (Exception $e) {
                                echo '<div class="alert alert-danger">Lỗi cập nhật trạng thái đơn hàng: ' . $e->getMessage() . '</div>';
                            }
                        } else {
                            error_log("VNPAY Callback - Payment failed with code: $vnp_ResponseCode");
                            // Thanh toán thất bại
                            $errorMessage = getVnpayErrorMessage($vnp_ResponseCode);
                            echo '<div class="alert alert-danger">Thanh toán thất bại! ' . $errorMessage . '</div>';
                            
                            // Cập nhật trạng thái đơn hàng
                            try {
                                $status = ($vnp_ResponseCode === '24') ? 'CANCELED' : 'FAILED';
                                error_log("VNPAY Callback - Updating order status to: $status");
                                
                                $stmt = $pdo->prepare("
                                    UPDATE orders 
                                    SET status = ?, 
                                        vnp_response_code = ?
                                    WHERE id = ? AND status != 'PAID'
                                ");
                                $result = $stmt->execute([$status, $vnp_ResponseCode, $vnp_TxnRef]);
                                error_log("VNPAY Callback - Update status result: " . ($result ? "success" : "failed"));
                            } catch (Exception $e) {
                                error_log("VNPAY Callback - Error updating status: " . $e->getMessage());
                                echo '<div class="alert alert-danger">Lỗi cập nhật trạng thái đơn hàng: ' . $e->getMessage() . '</div>';
                            }
                        }
                    } else {
                        error_log("VNPAY Callback - Invalid hash");
                        // Chữ ký không hợp lệ
                        echo '<div class="alert alert-danger">Dữ liệu không hợp lệ!</div>';
                    }
                    ?>
                    
                    <div class="text-center mt-4">
                        <a href="transaction_search.php" class="btn btn-primary">Xem lịch sử giao dịch</a>
                        <a href="index.php" class="btn btn-secondary">Về trang chủ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>

<!-- Tự động chuyển về trang lịch sử giao dịch sau 3 giây -->
<script>
    setTimeout(function() {
        window.location.href = "transaction_search.php";
    }, 3000);
</script>