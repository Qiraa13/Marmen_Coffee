<?php
$page_title = 'Pesanan Saya';
require_once 'includes/header.php';
require_once 'config/database.php';

$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();
$user_id = $_SESSION['user_id'];

$success = isset($_GET['success']) ? $_GET['success'] : '';

// Get user orders
$query = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$orders = $stmt->fetchAll();
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-bag-check text-coffee"></i> Pesanan Saya</h2>
    <a href="menu.php" class="btn btn-coffee">
        <i class="bi bi-plus"></i> Pesan Lagi
    </a>
</div>

<?php if (empty($orders)): ?>
    <div class="text-center py-5">
        <i class="bi bi-bag-x" style="font-size: 5rem; color: #ccc;"></i>
        <h3 class="mt-3">Belum Ada Pesanan</h3>
        <p class="text-muted">Anda belum pernah melakukan pemesanan</p>
        <a href="menu.php" class="btn btn-coffee">Mulai Pesan</a>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($orders as $order): ?>
            <div class="col-md-6 mb-4">
                <div class="card coffee-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Pesanan #<?php echo $order['id']; ?></h6>
                        <span class="order-status status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Total Harga:</small>
                                <div class="fw-bold text-coffee">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Pembayaran:</small>
                                <div class="fw-bold"><?php echo ucfirst($order['payment_method']); ?></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Status Pembayaran:</small>
                            <div>
                                <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : ($order['payment_status'] == 'failed' ? 'danger' : 'warning'); ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($order['notes']): ?>
                            <div class="mb-3">
                                <small class="text-muted">Catatan:</small>
                                <div><?php echo htmlspecialchars($order['notes']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <small class="text-muted">Tanggal Pesanan:</small>
                            <div><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                        </div>
                        
                        <button class="btn btn-outline-coffee btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal for Order Details -->
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
function viewOrderDetails(orderId) {
    // This would typically load order details via AJAX
    // For now, we'll show a simple message
    document.getElementById('orderDetailsContent').innerHTML = 
        '<p>Detail pesanan #' + orderId + ' akan ditampilkan di sini.</p>' +
        '<p>Fitur ini dapat dikembangkan lebih lanjut dengan AJAX untuk memuat detail item pesanan.</p>';
    
    var modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();
}
</script>

<?php require_once 'includes/footer.php'; ?>
