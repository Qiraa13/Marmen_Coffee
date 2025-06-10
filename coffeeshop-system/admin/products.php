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
    
    if ($action === 'add' || $action === 'edit') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $category_id = $_POST['category_id'];
        $status = $_POST['status'];
        
        // Handle file upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = '../uploads/';
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = uniqid() . '.' . $file_extension;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image)) {
                // File uploaded successfully
            } else {
                $error = 'Gagal mengupload gambar';
            }
        }
        
        if ($action === 'add') {
            $query = "INSERT INTO products (name, description, price, stock, category_id, image, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $name);
            $stmt->bindParam(2, $description);
            $stmt->bindParam(3, $price);
            $stmt->bindParam(4, $stock);
            $stmt->bindParam(5, $category_id);
            $stmt->bindParam(6, $image);
            $stmt->bindParam(7, $status);
            
            if ($stmt->execute()) {
                $success = 'Produk berhasil ditambahkan';
            } else {
                $error = 'Gagal menambahkan produk';
            }
        } else {
            $id = $_POST['id'];
            if ($image) {
                $query = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?, status = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $name);
                $stmt->bindParam(2, $description);
                $stmt->bindParam(3, $price);
                $stmt->bindParam(4, $stock);
                $stmt->bindParam(5, $category_id);
                $stmt->bindParam(6, $image);
                $stmt->bindParam(7, $status);
                $stmt->bindParam(8, $id);
            } else {
                $query = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, status = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $name);
                $stmt->bindParam(2, $description);
                $stmt->bindParam(3, $price);
                $stmt->bindParam(4, $stock);
                $stmt->bindParam(5, $category_id);
                $stmt->bindParam(6, $status);
                $stmt->bindParam(7, $id);
            }
            
            if ($stmt->execute()) {
                $success = 'Produk berhasil diperbarui';
            } else {
                $error = 'Gagal memperbarui produk';
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        $query = "UPDATE products SET status = 'inactive' WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $id);
        
        if ($stmt->execute()) {
            $success = 'Produk berhasil dihapus';
        } else {
            $error = 'Gagal menghapus produk';
        }
    }
}

// Get products
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY p.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$query = "SELECT * FROM categories ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Manajemen Produk - Marmen Coffee and Space";
$active_menu = "products";
include 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Produk</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddModal()">
        <i class="fas fa-plus"></i> Tambah Produk
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

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <img src="../uploads/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                     alt="<?php echo $product['name']; ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            </td>
                            <td>
                                <strong><?php echo $product['name']; ?></strong><br>
                                <small class="text-muted"><?php echo substr($product['description'], 0, 50); ?>...</small>
                            </td>
                            <td><?php echo $product['category_name']; ?></td>
                            <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $product['stock'] <= 10 ? 'danger' : 'success'; ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $product['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($product['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo $product['name']; ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" id="action" value="add">
                    <input type="hidden" name="id" id="productId">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategori</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="price" class="form-label">Harga</label>
                                <input type="number" class="form-control" id="price" name="price" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="stock" class="form-label">Stok</label>
                                <input type="number" class="form-control" id="stock" name="stock" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Gambar Produk</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar</small>
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
    document.getElementById('modalTitle').textContent = 'Tambah Produk';
    document.getElementById('action').value = 'add';
    document.getElementById('productId').value = '';
    document.querySelector('#productModal form').reset();
}

function editProduct(product) {
    document.getElementById('modalTitle').textContent = 'Edit Produk';
    document.getElementById('action').value = 'edit';
    document.getElementById('productId').value = product.id;
    document.getElementById('name').value = product.name;
    document.getElementById('description').value = product.description;
    document.getElementById('price').value = product.price;
    document.getElementById('stock').value = product.stock;
    document.getElementById('category_id').value = product.category_id;
    document.getElementById('status').value = product.status;
    
    new bootstrap.Modal(document.getElementById('productModal')).show();
}

function deleteProduct(id, name) {
    if (confirm(`Apakah Anda yakin ingin menghapus produk "${name}"?`)) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
