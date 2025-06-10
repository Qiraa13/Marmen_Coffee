<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireCustomer();

$database = new Database();
$db = $database->getConnection();

// Get products
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active' 
          ORDER BY p.name";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$query = "SELECT * FROM categories ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Menu - Marmen Coffee and Space";
$active_menu = "menu";
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <h1>Selamat Datang di Marmen Coffee and Space</h1>
        <p>Nikmati kopi berkualitas tinggi dan suasana yang nyaman untuk bekerja, belajar, atau sekadar bersantai.</p>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Kategori</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action active" data-category="all">
                            Semua Produk
                        </a>
                        <?php foreach ($categories as $category): ?>
                            <a href="#" class="list-group-item list-group-item-action" data-category="<?php echo $category['id']; ?>">
                                <?php echo $category['name']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tentang Kami</h5>
                </div>
                <div class="card-body">
                    <p>Marmen Coffee and Space adalah tempat yang sempurna untuk menikmati kopi berkualitas tinggi dan makanan lezat dalam suasana yang nyaman.</p>
                    <p>Kami menyediakan ruang yang ideal untuk bekerja, belajar, atau sekadar bersantai dengan teman dan keluarga.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="row" id="products-container">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4 product-item fade-in" data-category="<?php echo $product['category_id']; ?>">
                        <div class="card h-100 product-card">
                            <img src="uploads/<?php echo $product['image'] ?: 'default.jpg'; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                <p class="card-text"><?php echo $product['description']; ?></p>
                                <p class="card-text"><small class="text-muted"><?php echo $product['category_name']; ?></small></p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h5 text-primary">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                        <span class="badge bg-info">Stok: <?php echo $product['stock']; ?></span>
                                    </div>
                                    <?php if ($product['stock'] > 0): ?>
                                        <button class="btn btn-primary w-100 mt-2 add-to-cart" 
                                                data-id="<?php echo $product['id']; ?>"
                                                data-name="<?php echo $product['name']; ?>"
                                                data-price="<?php echo $product['price']; ?>">
                                            <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100 mt-2" disabled>
                                            Stok Habis
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Cart functionality
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    function updateCartCount() {
        document.getElementById('cart-count').textContent = cart.reduce((sum, item) => sum + item.quantity, 0);
    }
    
    // Add to cart
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            
            const existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({id, name, price, quantity: 1});
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            
            // Show success message
            const toast = document.createElement('div');
            toast.className = 'toast position-fixed top-0 end-0 m-3';
            toast.innerHTML = `
                <div class="toast-body bg-success text-white">
                    ${name} ditambahkan ke keranjang!
                </div>
            `;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            setTimeout(() => toast.remove(), 3000);
        });
    });
    
    // Category filter
    document.querySelectorAll('[data-category]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const category = this.dataset.category;
            
            // Update active state
            document.querySelectorAll('[data-category]').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Filter products
            document.querySelectorAll('.product-item').forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    
    // Initialize cart count
    updateCartCount();
</script>

<?php include 'includes/footer.php'; ?>
