<?php
$page_title = 'Admin Dashboard';
$is_admin = true;
require_once '../includes/header.php';
require_once '../config/database.php';

$auth->requireAdmin();

$database = new Database();
$conn = $database->getConnection();

// Get statistics
$stats = [];

// Total products
$query = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
$stats['products'] = $conn->query($query)->fetch()['total'];

// Total orders today
$query = "SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()";
$stats['orders_today'] = $conn->query($query)->fetch()['total'];

// Total revenue today
$query = "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'paid'";
$stats['revenue_today'] = $conn->query($query)->fetch()['total'];

// Pending orders
$query = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'";
$stats['pending_orders'] = $conn->query($query)->fetch()['total'];

// Recent orders
$query = "SELECT o.*, u.full_name FROM orders o 
          JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC LIMIT 10";
$recent_orders = $conn->query($query)->fetchAll();

// Low stock products
$query = "SELECT * FROM products WHERE stock <= 10 AND status = 'active' ORDER BY stock ASC";
$low_stock = $conn->query($query)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-speedometer2 text-coffee"></i> Dashboard Admin</h2>
    <div>
        <a href="../index.php" class="btn btn-outline-coffee">
            <i class="bi bi-house"></i> Kembali ke Website
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stats-card" style="background: linear-gradient(135deg, #007bff, #0056b3);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $stats['products']; ?></h4>
                        <p class="mb-0">Total Produk</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-box" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stats-card" style="background: linear-gradient(135deg, #28a745, #1e7e34);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $stats['orders_today']; ?></h4>
                        <p class="mb-0">Pesanan Hari Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cart-check" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stats-card" style="background: linear-gradient(135deg, #17a2b8, #117a8b);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>Rp <?php echo number_format($stats['revenue_today'], 0, ',', '.'); ?></h4>
                        <p class="mb-0">Pendapatan Hari Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stats-card" style="background: linear-gradient(135deg, #ffc107, #d39e00);">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $stats['pending_orders']; ?></h4>
                        <p class="mb-0">Pesanan Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clock" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-md-8 mb-4">
        <div class="card admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Pesanan Terbaru</h5>
                <a href="orders.php" class="btn btn-sm btn-coffee">Lihat Semua</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_orders)): ?>
                    <p class="text-muted">Belum ada pesanan.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                        <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $order['status'] == 'pending' ? 'warning' : 
                                                    ($order['status'] == 'confirmed' ? 'info' : 
                                                    ($order['status'] == 'completed' ? 'success' : 'secondary')); 
                                            ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    <div class="col-md-4 mb-4">
        <div class="card admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle text-warning"></i> Stok Menipis</h5>
                <a href="products.php" class="btn btn-sm btn-coffee">Kelola Produk</a>
            </div>
            <div class="card-body">
                <?php if (empty($low_stock)): ?>
                    <p class="text-muted">Semua produk stoknya aman.</p>
                <?php else: ?>
                    <?php foreach ($low_stock as $product): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                            <div>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                <br>
                                <small class="text-danger">Stok: <?php echo $product['stock']; ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
