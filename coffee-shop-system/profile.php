<?php
$page_title = 'Profile';
require_once 'includes/header.php';
require_once 'config/database.php';

$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();
$user_id = $_SESSION['user_id'];

$message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    try {
        // Get current user data
        $query = "SELECT * FROM users WHERE id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch();
        
        // Check if email is already used by another user
        $query = "SELECT id FROM users WHERE email = :email AND id != :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            throw new Exception('Email sudah digunakan oleh user lain');
        }
        
        // Update basic info
        $query = "UPDATE users SET full_name = :full_name, email = :email, phone = :phone, address = :address WHERE id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        // Update password if provided
        if (!empty($new_password)) {
            if (empty($current_password)) {
                throw new Exception('Password saat ini harus diisi');
            }
            
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception('Password saat ini salah');
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception('Konfirmasi password tidak sama');
            }
            
            if (strlen($new_password) < 6) {
                throw new Exception('Password baru minimal 6 karakter');
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = :password WHERE id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
        }
        
        $_SESSION['full_name'] = $full_name;
        $message = '<div class="alert alert-success">Profile berhasil diupdate!</div>';
        
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// Get current user data
$query = "SELECT * FROM users WHERE id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch();
?>

<?php echo $message; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card coffee-card">
            <div class="card-header bg-coffee text-white">
                <h4 class="mb-0"><i class="bi bi-person"></i> Profile Saya</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            <small class="text-muted">Username tidak dapat diubah</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">No. Telepon</label>
                        <input type="tel" class="form-control" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <hr>
                    <h5>Ubah Password</h5>
                    <p class="text-muted">Kosongkan jika tidak ingin mengubah password</p>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <input type="password" class="form-control" name="current_password">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="new_password">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="confirm_password">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-coffee">
                        <i class="bi bi-check-circle"></i> Update Profile
                    </button>
                    <a href="index.php" class="btn btn-outline-coffee">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
