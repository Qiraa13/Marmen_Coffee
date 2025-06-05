<?php
$page_title = 'Kelola Produk';
$is_admin = true;
require_once '../includes/header.php';
require_once '../config/database.php';

$auth->requireAdmin();

$database = new Database();
$conn = $database->getConnection();

$message = '';

// Handle product operations

$upload_dir = '../uploads/';
$image_path = null;

if (!empty($_FILES['image']['name'])) {
    $image_name = time() . '_' . basename($_FILES['image']['name']);
    $target_file = $upload_dir . $image_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($imageFileType, $allowed_types) && getimagesize($_FILES['image']['tmp_name'])) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $image_name;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        try {
           $query = "INSERT INTO products (name, description, image, price, stock, category_id, status) 
                    VALUES (:name, :description, :image, :price, :stock, :category_id, :status)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $_POST['name']);
            $stmt->bindParam(':description', $_POST['description']);
            $stmt->bindParam(':image', $image_path);
            $stmt->bindParam(':price', $_POST['price']);
            $stmt->bindParam(':stock', $_POST['stock']);
            $stmt->bindParam(':category_id', $_POST['category_id']);
            $stmt->bindParam(':status', $_POST['status']);

            
            $message = '<div class="alert alert-success">Produk berhasil ditambahkan!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }

    $update_image_sql = '';
if ($image_path) {
    $update_image_sql = ', image = :image';
}

    if (isset($_POST['update_product'])) {
        try {
            $query = "UPDATE products SET name = :name, description = :description, 
          price = :price, stock = :stock, category_id = :category_id, 
          status = :status $update_image_sql WHERE id = :id";

$stmt = $conn->prepare($query);
$stmt->bindParam(':name', $_POST['name']);
$stmt->bindParam(':description', $_POST['description']);
$stmt->bindParam(':price', $_POST['price']);
$stmt->bindParam(':stock', $_POST['stock']);
$stmt->bindParam(':category_id', $_POST['category_id']);
$stmt->bindParam(':status', $_POST['status']);
if ($image_path) {
    $stmt->bindParam(':image', $image_path);
}
$stmt->bindParam(':id', $_POST['product_id']);

            
            $message = '<div class="alert alert-success">Produk berhasil diupdate!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
    
    if (isset($_POST['delete_product'])) {
        try {
            $query = "UPDATE products SET status = 'inactive' WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $_POST['product_id']);
            $stmt->execute();
            
            $message = '<div class="alert alert-success">Produk berhasil dihapus!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Get categories
$query = "SELECT * FROM categories ORDER BY name";
$categories = $conn->query($query)->fetchAll();

// Get products
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY p.created_at DESC";
$products = $conn->query($query)->fetchAll();
?>

<?php echo $message; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box text-coffee"></i> Kelola Produk</h2>
    <div>
        <button class="btn btn-coffee" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus"></i> Tambah Produk
        </button>
        <a href="dashboard.php" class="btn btn-outline-coffee">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Products Table -->
<div class="card admin-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
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
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($product['description']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $product['stock'] <= 10 ? 'warning' : 'success'; ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $product['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($product['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="delete_product" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirmDelete('Hapus produk ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Produk Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- Tambahkan enctype="multipart/form-data" di sini -->
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Produk *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Harga *</label>
                            <input type="number" class="form-control" name="price" min="0" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stok *</label>
                            <input type="number" class="form-control" name="stock" min="0" required>
                        </div>
                    </div>

                    <!-- Input gambar harus di luar row karena full-width -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Gambar Produk</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Kategori *</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" name="status" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_product" class="btn btn-coffee">Tambah Produk</button>
                </div>
            </form>
        </div>
    </div>
</div> 


<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- Tambahkan enctype="multipart/form-data" di sini -->
            <form method="POST" enctype="multipart/form-data" id="editProductForm">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Nama Produk *</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_price" class="form-label">Harga *</label>
                            <input type="number" class="form-control" name="price" id="edit_price" min="0" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_stock" class="form-label">Stok *</label>
                            <input type="number" class="form-control" name="stock" id="edit_stock" min="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_image" class="form-label">Gambar Produk</label>
                        <input type="file" class="form-control" name="image" id="edit_image" accept="image/*">
                    </div>

                    <div id="edit_preview_container" class="mb-2">
                        <img id="edit_preview_image" src="" class="img-fluid rounded" style="max-height:150px;">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_category_id" class="form-label">Kategori *</label>
                            <select class="form-select" name="category_id" id="edit_category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_status" class="form-label">Status *</label>
                            <select class="form-select" name="status" id="edit_status" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_product" class="btn btn-coffee">Update Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editProduct(product) {
    document.getElementById('edit_product_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_description').value = product.description;
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_stock').value = product.stock;
    document.getElementById('edit_category_id').value = product.category_id;
    document.getElementById('edit_status').value = product.status;

    // Set preview gambar
    document.getElementById('edit_preview_image').src = product.image 
        ? `../uploads/${product.image}` 
        : '';

    // Tampilkan modal
    var modal = new bootstrap.Modal(document.getElementById('editProductModal'));
    modal.show();
}

// Preview gambar saat file diubah
document.getElementById('edit_image').addEventListener('change', function (event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('edit_preview_image').src = URL.createObjectURL(file);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>

