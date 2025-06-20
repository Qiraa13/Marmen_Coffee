<?php
require_once 'config/database.php';
require_once 'config/session.php';

$page_title = "Tentang Kami - Marmen Coffee and Space";
$active_menu = "about";
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <h1>Tentang Marmen Coffee and Space</h1>
        <p>Lebih dari sekadar coffee shop, kami adalah ruang untuk berkreasi, berkolaborasi, dan menikmati hidup.</p>
    </div>
</div>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <h2>Cerita Kami</h2>
            <p class="lead">Marmen Coffee and Space lahir dari passion untuk menciptakan ruang yang sempurna bagi para pecinta kopi dan mereka yang membutuhkan tempat nyaman untuk bekerja atau bersantai.</p>
            
            <p>Didirikan pada tahun 2025, kami memulai perjalanan dengan visi sederhana: menyediakan kopi berkualitas tinggi dalam suasana yang hangat dan inspiratif. Nama "Marmen" sendiri berasal dari kombinasi kata "Marble" dan "Men", yang melambangkan kekuatan dan keanggunan dalam setiap cangkir kopi yang kami sajikan.</p>
            
            <p>Kami percaya bahwa kopi bukan hanya minuman, tetapi juga medium untuk membangun koneksi, menciptakan ide-ide brilian, dan menikmati momen-momen berharga dalam hidup.</p>
        </div>
        <div class="col-md-6">
            <img src="uploads/about-us.jpg" alt="Marmen Coffee and Space" class="img-fluid rounded shadow">
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-12">
            <h2 class="text-center mb-4">Mengapa Memilih Marmen?</h2>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-coffee fa-3x text-primary mb-3"></i>
                    <h5>Kopi Berkualitas Premium</h5>
                    <p>Kami menggunakan biji kopi pilihan terbaik dari berbagai daerah di Indonesia, diolah dengan teknik brewing yang sempurna.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-wifi fa-3x text-primary mb-3"></i>
                    <h5>Ruang Kerja Nyaman</h5>
                    <p>Dilengkapi dengan WiFi cepat, colokan listrik di setiap meja, dan suasana yang kondusif untuk produktivitas.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5>Komunitas Hangat</h5>
                    <p>Tempat berkumpulnya para profesional, mahasiswa, dan pecinta kopi untuk berbagi ide dan pengalaman.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-12 text-center">
            <img src="uploads/team.jpg" alt="Tim Marmen" class="img-fluid rounded shadow">
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card bg-primary text-white">
                <div class="card-body text-center py-5">
                    <h2>Visi & Misi Kami</h2>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h4>Visi</h4>
                            <p>Menjadi coffee shop terdepan yang menginspirasi kreativitas dan produktivitas melalui kopi berkualitas dan ruang yang nyaman.</p>
                        </div>
                        <div class="col-md-6">
                            <h4>Misi</h4>
                            <p>Menyediakan pengalaman kopi terbaik, menciptakan ruang kerja yang inspiratif, dan membangun komunitas yang solid.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5 mb-5">
        <div class="col-md-12 text-center">
            <h2>Kunjungi Kami</h2>
            <p class="lead">Rasakan pengalaman Marmen Coffee and Space secara langsung</p>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                <a href="index.php" class="btn btn-primary btn-lg">Lihat Menu</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-lg">Login untuk Memesan</a>
            <?php endif; ?>
            <a href="contact.php" class="btn btn-outline-primary btn-lg ms-2">Hubungi Kami</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
