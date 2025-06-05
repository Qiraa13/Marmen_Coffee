<?php
$page_title = 'Login';
require_once 'includes/header.php';

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = isset($_GET['message']) ? $_GET['message'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        $result = $auth->login($username, $password);
        if ($result['success']) {
            if ($result['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow coffee-card">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">
                    <i class="bi bi-cup-hot text-coffee"></i> Login
                </h3>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username atau Email</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-coffee w-100">Login</button>
                </form>
                
                <div class="text-center mt-3">
                    <p>Belum punya akun? <a href="register.php" class="text-coffee">Daftar di sini</a></p>
                    <small class="text-muted">
                        Demo Admin: username <strong>admin</strong>, password <strong>password</strong>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
