<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireCustomer();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit();
}

$order_id = $input['order_id'];
$database = new Database();
$db = $database->getConnection();

try {
    $db->beginTransaction();
    
    // Check if order exists and belongs to user
    $query = "SELECT status FROM orders WHERE id = ? AND user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $order_id);
    $stmt->bindParam(2, $_SESSION['user_id']);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Pesanan tidak ditemukan');
    }
    
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order['status'] !== 'pending') {
        throw new Exception('Pesanan tidak dapat dibatalkan');
    }
    
    // Get order items to restore stock
    $query = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $order_id);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Restore stock
    foreach ($items as $item) {
        $query = "UPDATE products SET stock = stock + ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $item['quantity']);
        $stmt->bindParam(2, $item['product_id']);
        $stmt->execute();
    }
    
    // Update order status
    $query = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $order_id);
    $stmt->execute();
    
    $db->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
