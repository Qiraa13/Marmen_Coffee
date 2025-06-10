<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireCustomer();

$database = new Database();
$db = $database->getConnection();

// Get user orders
$query = "SELECT o.*, COUNT(oi.id) as item_count 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          WHERE o.user_id = ? 
          GROUP BY o.id 
          ORDER BY o.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Coffee Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-coffee"></i> Coffee Shop
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-arrow-left"></i> Kembali ke Menu
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><i class="fas fa-receipt"></i> Pesanan Saya</h2>
        
        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-3x text-muted"></i>
                <h4 class="mt-3 text-muted">Belum Ada Pesanan</h4>
                <p class="text-muted">Anda belum pernah melakukan pesanan</p>
                <a href="index.php" class="btn btn-primary">Lihat Menu</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Pesanan #<?php echo $order['id']; ?></h6>
                                <span class="badge bg-<?php 
                                    echo match($order['status']) {
                                        'pending' => 'warning',
                                        'confirmed' => 'info',
                                        'preparing' => 'primary',
                                        'ready' => 'success',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>"><?php echo ucfirst($order['status']); ?></span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Tanggal:</small><br>
                                        <strong><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Total:</small><br>
                                        <strong>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></strong>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <small class="text-muted">Item:</small><br>
                                        <strong><?php echo $order['item_count']; ?> item</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Pembayaran:</small><br>
                                        <span class="badge bg-<?php 
                                            echo match($order['payment_status']) {
                                                'paid' => 'success',
                                                'pending' => 'warning',
                                                'failed' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>"><?php echo ucfirst($order['payment_status']); ?></span>
                                    </div>
                                </div>
                                <?php if ($order['notes']): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Catatan:</small><br>
                                        <em><?php echo htmlspecialchars($order['notes']); ?></em>
                                    </div>
                                <?php endif; ?>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetail(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-times"></i> Batalkan
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Order Detail Modal -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailContent">
                    <!-- Order detail will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewOrderDetail(orderId) {
            fetch(`get_order_detail.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('orderDetailContent').innerHTML = data.html;
                        new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
                    } else {
                        alert('Gagal memuat detail pesanan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });
        }

        function cancelOrder(orderId) {
            if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                fetch('cancel_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({order_id: orderId})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pesanan berhasil dibatalkan');
                        location.reload();
                    } else {
                        alert('Gagal membatalkan pesanan: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                });
            }
        }
    </script>
</body>
</html>
