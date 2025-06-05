<?php
$page_title = 'Kelola User';
$is_admin = true;
require_once '../includes/header.php';
require_once '../config/database.php';

$auth->requireAdmin();

$database = new Database();
$conn = $database->getConnection();

$message = '';

// Handle user operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_role'])) {
        try {
            $query = "UPDATE users SET role = :role WHERE id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':role', $_POST['role']);
            $stmt->bindParam(':user_id', $_POST['user_id']);
            $stmt->execute();
            
            $message = '<div class="alert alert-success">Role user berhasil diupdate!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
    
    if (isset($_POST['add_user'])) {
        try {
            // Check if username or email already exists
            $query = "SELECT id FROM users WHERE username = :username OR email = :email";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':username', $_POST['username']);
            $stmt->bindParam(':email', $_POST['email']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                throw new Exception('Username atau email sudah digunakan');
            }
            
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (username, email, password, full_name, phone, address, role) 
                     VALUES (:username, :email, :password, :full_name, :phone, :address, :role)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':username', $_POST['username']);
            $stmt->bindParam(':email', $_POST['email']);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':full_name', $_POST['full_name']);
            $stmt->bindParam(':phone', $_POST['phone']);
            $stmt->bindParam(':address', $_POST['address']);
            $stmt->bindParam(':role', $_POST['role']);
            $stmt->execute();
            
            $message = '<div class="alert alert-success">User berhasil ditambahkan!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Get users with filters
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT * FROM users WHERE 1=1";

if ($role_filter) {
    $query .= " AND role = :role";
}

if ($search) {
    $query .= " AND (username LIKE :search OR full_name LIKE :search OR email LIKE :search)";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);

if ($role_filter) {
    $stmt->bindParam(':role', $role_filter);
}

if ($search) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param);
}

$stmt->execute();
$users = $stmt->fetchAll();

// Get statistics
$query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$role_stats = $conn->query($query)->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<?php echo $message; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people text-coffee"></i> Kelola User</h2>
    <div>
        <button class="btn btn-coffee" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus"></i> Tambah User
        </button>
        <a href="dashboard.php" class="btn btn-outline-coffee">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stats-card" style="background: linear-gradient(135deg, #28a745, #1e7e34);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo isset($role_stats['customer']) ? $role_stats['customer'] : 0; ?></h4>
                        <p class="mb-0">Total Customer</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-person" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stats-card" style="background: linear-gradient(135deg, #007bff, #0056b3);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo isset($role_stats['admin']) ? $role_stats['admin'] : 0; ?></h4>
                        <p class="mb-0">Total Admin</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-person-gear" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stats-card" style="background: linear-gradient(135deg, #17a2b8, #117a8b);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo count($users); ?></h4>
                        <p class="mb-0">Total User</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card admin-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Cari User</label>
                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Username, nama, atau email...">
            </div>
            
            <div class="col-md-4">
                <label for="role" class="form-label">Filter Role</label>
                <select class="form-select" name="role">
                    <option value="">Semua Role</option>
                    <option value="customer" <?php echo $role_filter == 'customer' ? 'selected' : ''; ?>>Customer</option>
                    <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-coffee">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <a href="users.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card admin-card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-4">
                <i class="bi bi-person-x" style="font-size: 3rem; color: #ccc;"></i>
                <h5 class="mt-3">Tidak ada user ditemukan</h5>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'primary' : 'success'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button class="btn btn-sm btn-outline-primary" onclick="updateUserRole(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">No. Telepon</label>
                        <input type="tel" class="form-control" name="phone">
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control" name="address" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role *</label>
                            <select class="form-select" name="role" required>
                                <option value="customer">Customer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_user" class="btn btn-coffee">Tambah User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Role Modal -->
<div class="modal fade" id="updateRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Role User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="user_id" id="update_user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" id="update_username" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="update_full_name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="update_role" class="form-label">Role *</label>
                        <select class="form-select" name="role" id="update_role" required>
                            <option value="customer">Customer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_role" class="btn btn-coffee">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateUserRole(user) {
    document.getElementById('update_user_id').value = user.id;
    document.getElementById('update_username').value = user.username;
    document.getElementById('update_full_name').value = user.full_name;
    document.getElementById('update_role').value = user.role;
    
    var modal = new bootstrap.Modal(document.getElementById('updateRoleModal'));
    modal.show();
}
</script>

<?php require_once '../includes/footer.php'; ?>
