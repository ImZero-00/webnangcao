<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error'=>'Vui lòng đăng nhập']);
    exit;
}

require __DIR__ . '/db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['id'])) {
    http_response_code(400);
    echo json_encode(['error'=>'Invalid input']);
    exit;
}

$id = (int)$input['id'];
$session = session_id();

$stmt = $pdo->prepare("
  DELETE FROM cart_items 
  WHERE id = ? AND session_id = ?
");
$stmt->execute([$id, $session]);

echo json_encode(['success'=>true]);
