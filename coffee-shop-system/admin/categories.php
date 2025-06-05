<?php
$page_title = 'Kelola Kategori';
$is_admin = true;
require_once '../includes/header.php';
require_once '../config/database.php';

$auth->requireAdmin();

$database = new Database();
$conn = $database->getConnection();

$message = '';

// Handle category operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category'])) {
        try {
            $query = "INSERT INTO categories (name, description) VALUES (:name, :description)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $_POST['name']);
            $stmt->bindParam(':description', $_POST['description']);
            $stmt->execute();
            
            $message = '<div class="alert alert-success">Kategori berhasil ditambahkan!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
    
    if (isset($_POST['update_category'])) {
        try {
            $query = "UPDATE categories SET name = :name, description = :description WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $_POST['name']);
            $stmt->bindParam(':description', $_POST['description']);
            $stmt->bindParam(':id', $_POST['category_id']);
            $stmt->execute();
            
            $message = '<div class="alert alert-success">Kategori berhasil diupdate!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
    
    if (isset($_POST['delete_category'])) {
        try {
            // Check if category has products
            $query = "SELECT COUNT(*) as count FROM products WHERE category_id = :category_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':category_id', $_POST['category_id']);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                throw new Exception('Kategori tidak dapat dihapus karena masih memiliki produk');
            }
            
            $query = "DELETE FROM categories WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $_POST['category_id']);
            $stmt->execute();
            
            $message = '<div class="alert alert-success">Kategori berhasil dihapus!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Get categories with product count
$query = "SELECT c.*, COUNT(p.id) as product_count FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id ORDER BY c.name";
$categories = $conn->query($query)->fetchAll();
?>

<?php echo $message; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tags text-coffee"></i> Kelola Kategori</h2>
    <div>
        <button class="btn btn-coffee" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus"></i> Tambah Kategori
        </button>
        <a href="dashboard.php" class="btn btn-outline-coffee">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Categories Table -->
<div class="card admin-card">
    <div class="card-body">
        <?php if (empty($categories)): ?>
            <div class="text-center py-4">
                <i class="bi bi-tags" style="font-size: 3rem; color: #ccc;"></i>
                <h5 class="mt-3">Belum ada kategori</h5>
                <p class="text-muted">Tambahkan kategori untuk mengorganisir produk</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Produk</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo $category['product_count']; ?> produk</span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if ($category['product_count'] == 0): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                            <button type="submit" name="delete_category" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirmDelete('Hapus kategori ini?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled title="Kategori memiliki produk">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Kategori *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Deskripsi kategori (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_category" class="btn btn-coffee">Tambah Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="category_id" id="edit_category_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Nama Kategori *</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_category" class="btn btn-coffee">Update Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(category) {
    document.getElementById('edit_category_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_description').value = category.description;
    
    var modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
}
</script>

<?php require_once '../includes/footer.php'; ?>
