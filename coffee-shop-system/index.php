<?php
// Handle 404 for invalid parameters
if (isset($_GET['page']) && !empty($_GET['page'])) {
    $allowed_pages = ['home', 'about', 'contact'];
    if (!in_array($_GET['page'], $allowed_pages)) {
        include '404.php';
        exit();
    }
}
$page_title = 'Home';
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Get featured products
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active' 
          ORDER BY p.created_at DESC LIMIT 6";
$featured_products = $conn->query($query)->fetchAll();
?>

<!-- Hero Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="hero-section text-white p-5">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold">Selamat Datang di Marmen Coffee and Space</h1>
                    <p class="lead">Nikmati kopi terbaik dengan cita rasa yang tak terlupakan. Dibuat dengan biji kopi pilihan dan racikan barista berpengalaman.</p>
                    <?php if (!$auth->isLoggedIn()): ?>
                        <a href="register.php" class="btn btn-light btn-lg me-3">Daftar Sekarang</a>
                        <a href="login.php" class="btn btn-outline-light btn-lg">Login</a>
                    <?php else: ?>
                        <a href="menu.php" class="btn btn-light btn-lg">Lihat Menu</a>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 text-center">
                    <img src="CAFE.jpg" alt="Gambar Cangkir" style="width: 400px; height: 300px; opacity: 0.8;">


                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="row mb-5">
    <div class="col-12">
        <h2 class="text-center mb-5">Mengapa Memilih Kami?</h2>
    </div>
    <div class="col-md-4 mb-4">
        <div class="text-center">
            <div class="feature-icon">
                <i class="bi bi-award" style="font-size: 2rem; color: #8B4513;"></i>
            </div>
            <h4>Kualitas Terbaik</h4>
            <p>Menggunakan biji kopi pilihan dari petani lokal terbaik dengan standar kualitas tinggi</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="text-center">
            <div class="feature-icon">
                <i class="bi bi-clock" style="font-size: 2rem; color: #8B4513;"></i>
            </div>
            <h4>Pelayanan Cepat</h4>
            <p>Pesanan Anda akan diproses dengan cepat dan efisien oleh tim berpengalaman</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="text-center">
            <div class="feature-icon">
                <i class="bi bi-heart" style="font-size: 2rem; color: #8B4513;"></i>
            </div>
            <h4>Dibuat dengan Cinta</h4>
            <p>Setiap cangkir dibuat dengan perhatian dan dedikasi tinggi untuk kepuasan Anda</p>
        </div>
    </div>
</div>

<!-- Featured Products -->
<?php if (!empty($featured_products)): ?>
<div class="row mb-5">
    <div class="col-12">
        <h2 class="text-center mb-5">Menu Unggulan</h2>
    </div>
    <?php foreach ($featured_products as $product): ?>
        <div class="col-md-4 mb-4">
            <div class="card coffee-card h-100">
                <div class="product-image card-img-top d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="bi bi-cup-hot" style="font-size: 4rem; color: #8B4513;"></i>
                </div>
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="card-text text-muted small"><?php echo htmlspecialchars($product['category_name']); ?></p>
                    <p class="card-text flex-grow-1"><?php echo htmlspecialchars($product['description']); ?></p>
                    <h5 class="text-coffee mt-auto">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></h5>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="col-12 text-center">
        <?php if ($auth->isLoggedIn()): ?>
            <a href="menu.php" class="btn btn-coffee btn-lg">Lihat Semua Menu</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-coffee btn-lg">Login untuk Memesan</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Call to Action -->
<?php if (!$auth->isLoggedIn()): ?>
<div class="row">
    <div class="col-12">
        <div class="bg-light p-5 rounded text-center">
            <h3>Siap untuk Menikmati Kopi Terbaik?</h3>
            <p class="lead">Bergabunglah dengan ribuan pelanggan yang sudah merasakan kelezatan kopi kami.</p>
            <a href="register.php" class="btn btn-coffee btn-lg">Daftar Sekarang</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
