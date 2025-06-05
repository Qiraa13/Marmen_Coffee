<?php
$page_title = 'Menu';
require_once 'includes/header.php';
require_once 'config/database.php';

$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();

$message = '';

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Check if item already in cart
        $query = "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Update quantity
            $query = "UPDATE cart SET quantity = quantity + :quantity WHERE user_id = :user_id AND product_id = :product_id";
        } else {
            // Insert new item
            $query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
        }
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->execute();
        
        $message = '<div class="alert alert-success">Produk berhasil ditambahkan ke keranjang!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// Get categories
$query = "SELECT * FROM categories ORDER BY name";
$categories = $conn->query($query)->fetchAll();

// Get products
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'";

if ($category_filter) {
    $query .= " AND p.category_id = :category_id";
}

if ($search) {
    $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
}

$query .= " ORDER BY p.name";

$stmt = $conn->prepare($query);

if ($category_filter) {
    $stmt->bindParam(':category_id', $category_filter);
}

if ($search) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param);
}

$stmt->execute();
$products = $stmt->fetchAll();
?>

<?php echo $message; ?>

<div class="row">
    <div class="col-md-3">
        <div class="card coffee-card">
            <div class="card-header bg-coffee text-white">
                <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter Menu</h5>
            </div>
            <div class="card-body">
                <form method="GET">
                    <div class="mb-3">
                        <label for="search" class="form-label">Cari Produk</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" placeholder="Nama produk...">
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-coffee w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="menu.php" class="btn btn-outline-coffee w-100 mt-2">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-cup-hot text-coffee"></i> Menu Marmen Coffee and Space</h2>
            <a href="cart.php" class="btn btn-coffee">
                <i class="bi bi-cart"></i> Lihat Keranjang
            </a>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Tidak ada produk yang ditemukan.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card coffee-card h-100 product-card">
                            <div class="product-image card-img-top d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-cup-hot" style="font-size: 4rem; color: #8B4513;"></i>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                <p class="card-text flex-grow-1"><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="h5 text-coffee mb-0">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                        <small class="text-muted">Stok: <?php echo $product['stock']; ?></small>
                                    </div>
                                    
                                    <?php if ($product['stock'] > 0): ?>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?php echo $product['stock']; ?>" style="width: 80px;">
                                            <button type="submit" name="add_to_cart" class="btn btn-coffee flex-grow-1">
                                                <i class="bi bi-cart-plus"></i> Tambah
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="bi bi-x-circle"></i> Stok Habis
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
