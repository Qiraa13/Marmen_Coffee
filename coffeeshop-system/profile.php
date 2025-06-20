<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireCustomer();

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Get user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_POST) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    try {
        // Check if email is already used by another user
        $query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->bindParam(2, $_SESSION['user_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            throw new Exception('Email sudah digunakan oleh pengguna lain');
        }
        
        // Update profile
        $query = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $full_name);
        $stmt->bindParam(2, $email);
        $stmt->bindParam(3, $phone);
        $stmt->bindParam(4, $address);
        $stmt->bindParam(5, $_SESSION['user_id']);
        $stmt->execute();
        
        // Update password if provided
        if (!empty($new_password)) {
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception('Password lama tidak benar');
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception('Konfirmasi password tidak sama');
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $hashed_password);
            $stmt->bindParam(2, $_SESSION['user_id']);
            $stmt->execute();
        }
        
        $_SESSION['full_name'] = $full_name;
        $success = 'Profil berhasil diperbarui';
        
        // Refresh user data
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $_SESSION['user_id']);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$page_title = "Profil - Marmen Coffee and Space";
$active_menu = "profile";
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user"></i> Profil Saya</h4>
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
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" disabled>
                                <small class="text-muted">Username tidak dapat diubah</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo $user['address']; ?></textarea>
                        </div>
                        
                        <hr>
                        <h5>Ubah Password</h5>
                        <p class="text-muted">Kosongkan jika tidak ingin mengubah password</p>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Lama</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Informasi Akun</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Bergabung sejak:</strong><br>
                            <?php echo date('d F Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Terakhir diperbarui:</strong><br>
                            <?php echo date('d F Y H:i', strtotime($user['updated_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Password validation
    document.getElementById('new_password').addEventListener('input', function() {
        const currentPassword = document.getElementById('current_password');
        if (this.value) {
            currentPassword.required = true;
        } else {
            currentPassword.required = false;
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
