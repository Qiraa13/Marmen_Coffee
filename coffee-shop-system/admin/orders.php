<?php
$page_title = 'Kelola Pesanan';
$is_admin = true;
require_once '../includes/header.php';
require_once '../config/database.php';

$auth->requireAdmin();

$database = new Database();
$conn = $database->getConnection();

$message = '';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        try {
            $query = "UPDATE orders SET status = :status WHERE id = :order_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':status', $_POST['status']);
            $stmt->bindParam(':order_id', $_POST['order_id']);
            $stmt->execute();
            
            $message = '<div class="alert alert-success">Status pesanan berhasil diupdate!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
    
    if (isset($_POST['update_payment'])) {
        try {
            $query = "UPDATE orders SET payment_status = :payment_status WHERE id = :order_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':payment_status', $_POST['payment_status']);
            $stmt->bindParam(':order_id', $_POST['order_id']);
            $stmt->execute();
            
            $message = '<div class="alert alert-success">Status pembayaran berhasil diupdate!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Get orders with filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$payment_filter = isset($_GET['payment']) ? $_GET['payment'] : '';

$query = "SELECT o.*, u.full_name, u.phone FROM orders o 
          JOIN users u ON o.user_id = u.id WHERE 1=1";

if ($status_filter) {
    $query .= " AND o.status = :status";
}

if ($payment_filter) {
    $query .= " AND o.payment_status = :payment_status";
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($query);

if ($status_filter) {
    $stmt->bindParam(':status', $status_filter);
}

if ($payment_filter) {
    $stmt->bindParam(':payment_status', $payment_filter);
}

$stmt->execute();
$orders = $stmt->fetchAll();
?>

<?php echo $message; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cart-check text-coffee"></i> Kelola Pesanan</h2>
    <a href="dashboard.php" class="btn btn-outline-coffee">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<!-- Filters -->
<div class="card admin-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">Filter Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="preparing" <?php echo $status_filter == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                    <option value="ready" <?php echo $status_filter == 'ready' ? 'selected' : ''; ?>>Ready</option>
                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="payment" class="form-label">Filter Pembayaran</label>
                <select class="form-select" name="payment">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo $payment_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="paid" <?php echo $payment_filter == 'paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="failed" <?php echo $payment_filter == 'failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-coffee">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="orders.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card admin-card">
    <div class="card-body">
        <?php if (empty($orders)): ?>
            <div class="text-center py-4">
                <i class="bi bi-cart-x" style="font-size: 3rem; color: #ccc;"></i>
                <h5 class="mt-3">Tidak ada pesanan</h5>
                <p class="text-muted">Belum ada pesanan yang masuk</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Metode</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                                    <?php if ($order['phone']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($order['phone']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : ($order['payment_status'] == 'failed' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo ucfirst($order['payment_method']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="updateOrderStatus(<?php echo htmlspecialchars(json_encode($order)); ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="order_id" id="update_order_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="update_status" class="form-label">Status Pesanan</label>
                        <select class="form-select" name="status" id="update_status" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="preparing">Preparing</option>
                            <option value="ready">Ready</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="update_payment_status" class="form-label">Status Pembayaran</label>
                        <select class="form-select" name="payment_status" id="update_payment_status" required>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_status" class="btn btn-coffee">Update Status</button>
                    <button type="submit" name="update_payment" class="btn btn-success">Update Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function updateOrderStatus(order) {
    document.getElementById('update_order_id').value = order.id;
    document.getElementById('update_status').value = order.status;
    document.getElementById('update_payment_status').value = order.payment_status;
    
    var modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
}

function viewOrderDetails(orderId) {
    // This would typically load order details via AJAX
    document.getElementById('orderDetailsContent').innerHTML = 
        '<div class="text-center"><div class="spinner-border" role="status"></div><p>Loading...</p></div>';
    
    var modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();
    
    // Simulate loading order details
    setTimeout(() => {
        document.getElementById('orderDetailsContent').innerHTML = 
            '<p>Detail pesanan #' + orderId + ' akan ditampilkan di sini.</p>' +
            '<p>Fitur ini dapat dikembangkan lebih lanjut dengan AJAX untuk memuat detail item pesanan, alamat pengiriman, dll.</p>';
    }, 1000);
}
</script>

<?php require_once '../includes/footer.php'; ?>
