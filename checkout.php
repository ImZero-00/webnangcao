<?php
// checkout.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session & include DB
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/db.php';

// Bảo đảm user đã login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Xử lý POST tạo đơn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Đọc dữ liệu JSON từ request
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON: ' . json_last_error_msg());
        }
        
        $ids = $input['ids'] ?? [];
        if (empty($ids)) {
            throw new Exception('Không có sản phẩm nào được chọn');
        }
        
        // Lấy session ID hiện tại
        $session = session_id();
        
        // Bắt đầu transaction
        $pdo->beginTransaction();
        
        // Lấy thông tin về cart items từ database
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmtItems = $pdo->prepare("
            SELECT id, product_id, quantity 
            FROM cart_items
            WHERE id IN ($placeholders) AND session_id = ?
        ");
        
        // Thêm tất cả các tham số vào mảng params
        $params = $ids;
        $params[] = $session;
        
        $stmtItems->execute($params);
        $cartItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($cartItems)) {
            throw new Exception('Không tìm thấy sản phẩm hợp lệ trong giỏ hàng');
        }
        
        // Lấy thông tin sản phẩm từ API giống như trong cart.php
        $apiUrl = 'http://' . $_SERVER['HTTP_HOST']
                . dirname($_SERVER['SCRIPT_NAME'])
                . '/products_api.php';
        
        $json = @file_get_contents($apiUrl);
        $allProducts = $json ? json_decode($json, true) : [];
        if (!is_array($allProducts)) {
            throw new Exception('Không thể lấy dữ liệu sản phẩm từ API');
        }
        
        // Build map [id => product]
        $prodMap = [];
        foreach ($allProducts as $p) {
            if (isset($p['id'])) {
                $prodMap[$p['id']] = $p;
            }
        }
        
        // Tính tổng đơn hàng và tạo danh sách items có đầy đủ thông tin
        $total = 0;
        $orderItems = [];
        
        foreach ($cartItems as $item) {
            $prodId = $item['product_id'];
            if (!isset($prodMap[$prodId])) {
                continue; // Bỏ qua nếu không tìm thấy sản phẩm
            }
            
            $prod = $prodMap[$prodId];
            $price = (float)$prod['price'];
            $quantity = (int)$item['quantity'];
            $subtotal = $price * $quantity;
            
            $total += $subtotal;
            
            $orderItems[] = [
                'product_id' => $prodId,
                'quantity' => $quantity,
                'price' => $price
            ];
        }
        
        if (empty($orderItems)) {
            throw new Exception('Không có sản phẩm hợp lệ để thanh toán');
        }
        
        // Lưu order
        $stmtOrder = $pdo->prepare(
            "INSERT INTO orders (user_id, total, status) 
             VALUES (?, ?, 'INIT')"
        );
        $stmtOrder->execute([$_SESSION['user_id'], $total]);
        $orderId = $pdo->lastInsertId();
        
        // Lưu chi tiết order_items
        $stmtOrderItem = $pdo->prepare(
            "INSERT INTO order_items (order_id, product_id, quantity, price) 
             VALUES (?, ?, ?, ?)"
        );
        
        foreach ($orderItems as $item) {
            $stmtOrderItem->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
        }
        
        $pdo->commit();
        
        // Trả về kết quả thành công
        echo json_encode([
            'success' => true,
            'orderId' => $orderId,
            'total' => $total
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Log lỗi
        error_log('Checkout error: ' . $e->getMessage());
        
        // Trả về lỗi dưới dạng JSON
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Xử lý GET để redirect thanh toán
if (isset($_GET['order_id'])) {
    $orderId = (int) $_GET['order_id'];
    
    // Kiểm tra xem order có tồn tại và thuộc về user hiện tại không
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        header('Location: payment_confirm.php?order_id=' . $orderId);
        exit;
    } else {
        http_response_code(403);
        echo 'Đơn hàng không tồn tại hoặc không thuộc về bạn';
        exit;
    }
}

// Nếu không phải POST và không có order_id thì có thể show lỗi hoặc redirect
http_response_code(400);
echo 'Bad request';
?>