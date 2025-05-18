<?php
// Thêm cho chắc chắn
ob_start();

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error'=>'Vui lòng đăng nhập để thêm vào giỏ hàng']);
    exit;
}

require __DIR__ . '/db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['productId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'productId missing']);
    exit;
}

$sessionId = session_id();
$productId = (int)$input['productId'];

try {
    // Kiểm tra xem đã có trong giỏ chưa
    $stmt = $pdo->prepare(
      "SELECT id, quantity 
       FROM cart_items 
       WHERE session_id = ? AND product_id = ?"
    );
    $stmt->execute([$sessionId, $productId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        // Tăng số lượng
        $upd = $pdo->prepare(
          "UPDATE cart_items 
           SET quantity = quantity + 1 
           WHERE id = ?"
        );
        $upd->execute([$item['id']]);
    } else {
        // Thêm mới
        $ins = $pdo->prepare(
          "INSERT INTO cart_items (session_id, product_id, quantity) 
           VALUES (?, ?, 1)"
        );
        $ins->execute([$sessionId, $productId]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

ob_end_flush();