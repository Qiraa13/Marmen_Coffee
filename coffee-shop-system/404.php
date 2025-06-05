<?php
http_response_code(404);
$page_title = '404 - Halaman Tidak Ditemukan';
require_once 'includes/header.php';
?>

<div class="text-center py-5">
    <div class="mb-4">
        <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 8rem;"></i>
    </div>
    
    <h1 class="display-1 fw-bold text-coffee">404</h1>
    <h2 class="mb-4">Halaman Tidak Ditemukan</h2>
    <p class="lead mb-4">Maaf, halaman yang Anda cari tidak dapat ditemukan atau telah dipindahkan.</p>
    
    <div class="mb-4">
        <a href="index.php" class="btn btn-coffee btn-lg me-3">
            <i class="bi bi-house"></i> Kembali ke Beranda
        </a>
        <?php if ($auth->isLoggedIn()): ?>
            <a href="menu.php" class="btn btn-outline-coffee btn-lg">
                <i class="bi bi-cup-hot"></i> Lihat Menu
            </a>
        <?php else: ?>
            <a href="login.php" class="btn btn-outline-coffee btn-lg">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </a>
        <?php endif; ?>
    </div>
    
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card coffee-card">
                <div class="card-body">
                    <h5 class="card-title">Mungkin Anda mencari:</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-coffee"><i class="bi bi-house"></i> Beranda</a></li>
                        <?php if ($auth->isLoggedIn()): ?>
                            <li><a href="menu.php" class="text-coffee"><i class="bi bi-cup-hot"></i> Menu Kopi</a></li>
                            <li><a href="cart.php" class="text-coffee"><i class="bi bi-cart"></i> Keranjang</a></li>
                            <li><a href="orders.php" class="text-coffee"><i class="bi bi-bag-check"></i> Pesanan Saya</a></li>
                            <li><a href="profile.php" class="text-coffee"><i class="bi bi-person"></i> Profile</a></li>
                            <?php if ($auth->isAdmin()): ?>
                                <li><a href="admin/dashboard.php" class="text-coffee"><i class="bi bi-speedometer2"></i> Dashboard Admin</a></li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li><a href="login.php" class="text-coffee"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                            <li><a href="register.php" class="text-coffee"><i class="bi bi-person-plus"></i> Daftar</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.text-coffee:hover {
    color: #A0522D !important;
    text-decoration: none;
}
</style>

<?php require_once 'includes/footer.php'; ?>
