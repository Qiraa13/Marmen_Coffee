<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

// Total products
$query = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total orders today
$query = "SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['orders_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total revenue today
$query = "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['revenue_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pending orders
$query = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Recent orders
$query = "SELECT o.*, u.full_name 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC 
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Low stock products
$query = "SELECT * FROM products WHERE stock <= 10 AND status = 'active' ORDER BY stock ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Admin Dashboard - Marmen Coffee and Space";
$active_menu = "dashboard";
include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary">
            <i class="fas fa-calendar"></i> Hari Ini
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card stat-primary mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $stats['products']; ?></h4>
                        <p class="mb-0 text-muted">Total Produk</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-box fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card stat-success mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $stats['orders_today']; ?></h4>
                        <p class="mb-0 text-muted">Pesanan Hari Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-receipt fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card stat-info mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>Rp <?php echo number_format($stats['revenue_today'], 0, ',', '.'); ?></h4>
                        <p class="mb-0 text-muted">Pendapatan Hari Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card stat-warning mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $stats['pending_orders']; ?></h4>
                        <p class="mb-0 text-muted">Pesanan Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Pesanan Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo $order['full_name']; ?></td>
                                    <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center">
                    <a href="orders.php" class="btn btn-primary">Lihat Semua Pesanan</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Stok Menipis</h5>
            </div>
            <div class="card-body">
                <?php if (empty($low_stock)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                        <p class="mt-3 text-muted">Semua produk memiliki stok yang cukup</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($low_stock as $product): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 border-bottom">
                            <div>
                                <strong><?php echo $product['name']; ?></strong><br>
                                <small class="text-muted">Stok: <?php echo $product['stock']; ?></small>
                            </div>
                            <span class="badge bg-danger"><?php echo $product['stock']; ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center mt-3">
                        <a href="products.php" class="btn btn-warning btn-sm">Update Stok</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Akses Cepat</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="products.php?action=add" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus"></i> Tambah Produk Baru
                    </a>
                    <a href="categories.php?action=add" class="list-group-item list-group-item-action">
                        <i class="fas fa-folder-plus"></i> Tambah Kategori Baru
                    </a>
                    <a href="orders.php?status=pending" class="list-group-item list-group-item-action">
                        <i class="fas fa-clock"></i> Lihat Pesanan Pending
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
