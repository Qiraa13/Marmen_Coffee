<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireCustomer();

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit();
}

$order_id = $_GET['id'];
$database = new Database();
$db = $database->getConnection();

// Get order details
$query = "SELECT o.*, u.full_name 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          WHERE o.id = ? AND o.user_id = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $order_id);
$stmt->bindParam(2, $_SESSION['user_id']);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit();
}

$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Get order items
$query = "SELECT oi.*, p.name as product_name, p.image 
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.id 
          WHERE oi.order_id = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $order_id);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$html = '
<div class="row">
    <div class="col-md-6">
        <h6>Informasi Pesanan</h6>
        <table class="table table-sm">
            <tr><td>ID Pesanan:</td><td>#' . $order['id'] . '</td></tr>
            <tr><td>Tanggal:</td><td>' . date('d/m/Y H:i', strtotime($order['created_at'])) . '</td></tr>
            <tr><td>Status:</td><td><span class="badge bg-primary">' . ucfirst($order['status']) . '</span></td></tr>
            <tr><td>Pembayaran:</td><td><span class="badge bg-success">' . ucfirst($order['payment_status']) . '</span></td></tr>
            <tr><td>Metode:</td><td>' . ucfirst($order['payment_method']) . '</td></tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6>Detail Pembeli</h6>
        <table class="table table-sm">
            <tr><td>Nama:</td><td>' . $order['full_name'] . '</td></tr>
            <tr><td>Total:</td><td><strong>Rp ' . number_format($order['total_amount'], 0, ',', '.') . '</strong></td></tr>
        </table>
        ' . ($order['notes'] ? '<p><strong>Catatan:</strong><br>' . htmlspecialchars($order['notes']) . '</p>' : '') . '
    </div>
</div>

<h6>Item Pesanan</h6>
<div class="table-responsive">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Harga</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>';

foreach ($items as $item) {
    $html .= '
            <tr>
                <td>' . $item['product_name'] . '</td>
                <td>Rp ' . number_format($item['price'], 0, ',', '.') . '</td>
                <td>' . $item['quantity'] . '</td>
                <td>Rp ' . number_format($item['subtotal'], 0, ',', '.') . '</td>
            </tr>';
}

$html .= '
        </tbody>
        <tfoot>
            <tr class="table-dark">
                <th colspan="3">Total</th>
                <th>Rp ' . number_format($order['total_amount'], 0, ',', '.') . '</th>
            </tr>
        </tfoot>
    </table>
</div>';

echo json_encode(['success' => true, 'html' => $html]);
?>
