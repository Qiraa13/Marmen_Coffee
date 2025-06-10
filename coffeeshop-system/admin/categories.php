<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        
        // Check if category name already exists
        $query = "SELECT id FROM categories WHERE name = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $name);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'Nama kategori sudah ada';
        } else {
            $query = "INSERT INTO categories (name, description) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $name);
            $stmt->bindParam(2, $description);
            
            if ($stmt->execute()) {
                $success = 'Kategori berhasil ditambahkan';
            } else {
                $error = 'Gagal menambahkan kategori';
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        
        // Check if category name already exists (excluding current category)
        $query = "SELECT id FROM categories WHERE name = ? AND id != ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error = 'Nama kategori sudah ada';
        } else {
            $query = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $name);
            $stmt->bindParam(2, $description);
            $stmt->bindParam(3, $id);
            
            if ($stmt->execute()) {
                $success = 'Kategori berhasil diperbarui';
            } else {
                $error = 'Gagal memperbarui kategori';
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        
        // Check if category has products
        $query = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $error = 'Tidak dapat menghapus kategori yang masih memiliki produk';
        } else {
            $query = "DELETE FROM categories WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $id);
            
            if ($stmt->execute()) {
                $success = 'Kategori berhasil dihapus';
            } else {
                $error = 'Gagal menghapus kategori';
            }
        }
    }
}

// Get categories with product count
$query = "SELECT c.*, COUNT(p.id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id 
          ORDER BY c.name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Manajemen Kategori - Marmen Coffee and Space";
$active_menu = "categories";
include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Kategori</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openAddModal()">
        <i class="fas fa-plus"></i> Tambah Kategori
    </button>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Daftar Kategori</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th>Jumlah Produk</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><strong><?php echo $category['name']; ?></strong></td>
                                    <td><?php echo $category['description'] ?: '-'; ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $category['product_count']; ?> produk</span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($category['product_count'] == 0): ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo $category['name']; ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Tidak dapat dihapus karena masih memiliki produk">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Statistik Kategori</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Total Kategori</h6>
                    <h3 class="text-primary"><?php echo count($categories); ?></h3>
                </div>
                <div class="mb-3">
                    <h6>Kategori Terpopuler</h6>
                    <?php 
                    $popular = array_slice(array_filter($categories, function($cat) { return $cat['product_count'] > 0; }), 0, 3);
                    usort($popular, function($a, $b) { return $b['product_count'] - $a['product_count']; });
                    ?>
                    <?php if (!empty($popular)): ?>
                        <?php foreach ($popular as $cat): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo $cat['name']; ?></span>
                                <span class="badge bg-primary"><?php echo $cat['product_count']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Belum ada produk</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Tips Manajemen Kategori</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-lightbulb text-warning"></i> Gunakan nama kategori yang jelas dan mudah dipahami</li>
                    <li class="mb-2"><i class="fas fa-lightbulb text-warning"></i> Tambahkan deskripsi untuk menjelaskan jenis produk</li>
                    <li class="mb-2"><i class="fas fa-lightbulb text-warning"></i> Kategori tidak dapat dihapus jika masih memiliki produk</li>
                    <li><i class="fas fa-lightbulb text-warning"></i> Organisir produk dengan kategori yang tepat untuk memudahkan customer</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="action" value="add">
                    <input type="hidden" name="id" id="categoryId">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Deskripsi kategori (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Kategori';
    document.getElementById('action').value = 'add';
    document.getElementById('categoryId').value = '';
    document.querySelector('#categoryModal form').reset();
}

function editCategory(category) {
    document.getElementById('modalTitle').textContent = 'Edit Kategori';
    document.getElementById('action').value = 'edit';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('name').value = category.name;
    document.getElementById('description').value = category.description || '';
    
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

function deleteCategory(id, name) {
    if (confirm(`Apakah Anda yakin ingin menghapus kategori "${name}"?`)) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
