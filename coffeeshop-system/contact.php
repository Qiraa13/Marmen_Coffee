<?php
require_once 'config/database.php';
require_once 'config/session.php';

$success = '';
$error = '';

if ($_POST) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    // Here you would typically send an email or save to database
    // For now, we'll just show a success message
    $success = 'Pesan Anda berhasil dikirim! Kami akan segera menghubungi Anda.';
}

$page_title = "Kontak - Marmen Coffee and Space";
$active_menu = "contact";
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <h1>Hubungi Kami</h1>
        <p>Kami senang mendengar dari Anda. Jangan ragu untuk menghubungi kami kapan saja.</p>
    </div>
</div>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Kirim Pesan</h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo $_SESSION['full_name'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $_SESSION['email'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subjek</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Pilih Subjek</option>
                                <option value="Pertanyaan Umum">Pertanyaan Umum</option>
                                <option value="Reservasi">Reservasi</option>
                                <option value="Kerjasama">Kerjasama</option>
                                <option value="Keluhan">Keluhan</option>
                                <option value="Saran">Saran</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Pesan</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Kirim Pesan
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Kontak</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-map-marker-alt text-primary"></i>
                        <strong> Alamat</strong><br>
                        Jl. Tamin No. 1<br>
                        Bandar Lampung
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-phone text-primary"></i>
                        <strong> Telepon</strong><br>
                        +62 812 3456 7890
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-envelope text-primary"></i>
                        <strong> Email</strong><br>
                        info@marmencoffee.com
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-clock text-primary"></i>
                        <strong> Jam Buka</strong><br>
                        Senin - Jumat: 08:00 - 22:00<br>
                        Sabtu - Minggu: 10:00 - 23:00
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Media Sosial</h5>
                </div>
                <div class="card-body text-center">
                    <a href="#" class="btn btn-outline-primary btn-sm me-2 mb-2">
                        <i class="fab fa-facebook"></i> Facebook
                    </a>
                    <a href="#" class="btn btn-outline-primary btn-sm me-2 mb-2">
                        <i class="fab fa-instagram"></i> Instagram
                    </a>
                    <a href="#" class="btn btn-outline-primary btn-sm me-2 mb-2">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                    <a href="#" class="btn btn-outline-primary btn-sm mb-2">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Map Section -->
    <div class="row mt-5 mb-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Lokasi Kami</h5>
                </div>
                <div class="card-body p-0">
                    <div style="height: 400px; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Peta Lokasi</h5>
                            <p class="text-muted">Jl. Tamin No. 1, Bandar Lampung</p>
                            <small class="text-muted">*Integrasi dengan Google Maps akan ditambahkan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
