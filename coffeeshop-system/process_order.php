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

if (!$input || !isset($input['cart']) || empty($input['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    $db->beginTransaction();
    
    $cart = $input['cart'];
    $payment_method = $input['payment_method'] ?? 'cash';
    $notes = $input['notes'] ?? '';
    $total_amount = 0;
    
    // Calculate total and validate products
    foreach ($cart as $item) {
        $query = "SELECT price, stock FROM products WHERE id = ? AND status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $item['id']);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Produk tidak ditemukan: " . $item['name']);
        }
        
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product['stock'] < $item['quantity']) {
            throw new Exception("Stok tidak mencukupi untuk: " . $item['name']);
        }
        
        $total_amount += $product['price'] * $item['quantity'];
    }
    
    // Create order
    $query = "INSERT INTO orders (user_id, total_amount, payment_method, notes) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $_SESSION['user_id']);
    $stmt->bindParam(2, $total_amount);
    $stmt->bindParam(3, $payment_method);
    $stmt->bindParam(4, $notes);
    $stmt->execute();
    
    $order_id = $db->lastInsertId();
    
    // Add order items and update stock
    foreach ($cart as $item) {
        // Get current product data
        $query = "SELECT price FROM products WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $item['id']);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $subtotal = $product['price'] * $item['quantity'];
        
        // Insert order item
        $query = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $order_id);
        $stmt->bindParam(2, $item['id']);
        $stmt->bindParam(3, $item['quantity']);
        $stmt->bindParam(4, $product['price']);
        $stmt->bindParam(5, $subtotal);
        $stmt->execute();
        
        // Update stock
        $query = "UPDATE products SET stock = stock - ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $item['quantity']);
        $stmt->bindParam(2, $item['id']);
        $stmt->execute();
    }
    
    $db->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
