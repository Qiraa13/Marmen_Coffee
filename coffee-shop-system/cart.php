<?php
$page_title = 'Keranjang Belanja';
require_once 'includes/header.php';
require_once 'config/database.php';

$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();
$user_id = $_SESSION['user_id'];

$message = '';

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        $cart_id = $_POST['cart_id'];
        $quantity = $_POST['quantity'];
        
        try {
            $query = "UPDATE cart SET quantity = :quantity WHERE id = :cart_id AND user_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':cart_id', $cart_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $message = '<div class="alert alert-success">Keranjang berhasil diupdate!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
    
    if (isset($_POST['remove_item'])) {
        $cart_id = $_POST['cart_id'];
        
        try {
            $query = "DELETE FROM cart WHERE id = :cart_id AND user_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':cart_id', $cart_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $message = '<div class="alert alert-success">Item berhasil dihapus dari keranjang!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
    
    if (isset($_POST['checkout'])) {
        try {
            $conn->beginTransaction();
            
            // Get cart items
            $query = "SELECT c.*, p.name, p.price, p.stock FROM cart c 
                     JOIN products p ON c.product_id = p.id 
                     WHERE c.user_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $cart_items = $stmt->fetchAll();
            
            if (empty($cart_items)) {
                throw new Exception('Keranjang kosong');
            }
            
            // Check stock availability
            foreach ($cart_items as $item) {
                if ($item['quantity'] > $item['stock']) {
                    throw new Exception('Stok ' . $item['name'] . ' tidak mencukupi');
                }
            }
            
            // Calculate total
            $total_amount = 0;
            foreach ($cart_items as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }
            
            // Create order
            $query = "INSERT INTO orders (user_id, total_amount, payment_method, notes) 
                     VALUES (:user_id, :total_amount, :payment_method, :notes)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':total_amount', $total_amount);
            $stmt->bindParam(':payment_method', $_POST['payment_method']);
            $stmt->bindParam(':notes', $_POST['notes']);
            $stmt->execute();
            
            $order_id = $conn->lastInsertId();
            
            // Add order items and update stock
            foreach ($cart_items as $item) {
                // Add order item
                $query = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                         VALUES (:order_id, :product_id, :quantity, :price, :subtotal)";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':product_id', $item['product_id']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':price', $item['price']);
                $subtotal = $item['price'] * $item['quantity'];
                $stmt->bindParam(':subtotal', $subtotal);
                $stmt->execute();
                
                // Update product stock
                $query = "UPDATE products SET stock = stock - :quantity WHERE id = :product_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':product_id', $item['product_id']);
                $stmt->execute();
            }
            
            // Clear cart
            $query = "DELETE FROM cart WHERE user_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $conn->commit();
            
            header('Location: orders.php?success=Pesanan berhasil dibuat');
            exit();
            
        } catch (Exception $e) {
            $conn->rollBack();
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Get cart items
$query = "SELECT c.*, p.name, p.description, p.price, p.stock FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = :user_id 
          ORDER BY c.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$cart_items = $stmt->fetchAll();

$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}
?>

<?php echo $message; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cart text-coffee"></i> Keranjang Belanja</h2>
    <a href="menu.php" class="btn btn-outline-coffee">
        <i class="bi bi-arrow-left"></i> Kembali ke Menu
    </a>
</div>

<?php if (empty($cart_items)): ?>
    <div class="text-center py-5">
        <i class="bi bi-cart-x" style="font-size: 5rem; color: #ccc;"></i>
        <h3 class="mt-3">Keranjang Kosong</h3>
        <p class="text-muted">Belum ada item di keranjang Anda</p>
        <a href="menu.php" class="btn btn-coffee">Mulai Belanja</a>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-8">
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="product-image d-flex align-items-center justify-content-center" style="height: 80px;">
                                <i class="bi bi-cup-hot" style="font-size: 2rem; color: #8B4513;"></i>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($item['description']); ?></p>
                            <small class="text-muted">Stok: <?php echo $item['stock']; ?></small>
                        </div>
                        <div class="col-md-2">
                            <strong>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></strong>
                        </div>
                        <div class="col-md-2">
                            <form method="POST" id="update_form_<?php echo $item['id']; ?>">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" 
                                            onclick="updateQuantity(<?php echo $item['id']; ?>, 'decrease')">-</button>
                                    <input type="number" name="quantity" id="quantity_<?php echo $item['id']; ?>" 
                                           class="form-control text-center" value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="<?php echo $item['stock']; ?>" style="max-width: 60px;">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" 
                                            onclick="updateQuantity(<?php echo $item['id']; ?>, 'increase')">+</button>
                                </div>
                                <input type="hidden" name="update_cart" value="1">
                            </form>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="mb-2">
                                <strong>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></strong>
                            </div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="remove_item" class="btn btn-outline-danger btn-sm" 
                                        onclick="return confirmDelete('Hapus item dari keranjang?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card coffee-card">
                <div class="card-header bg-coffee text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Ringkasan Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Item:</span>
                        <span><?php echo count($cart_items); ?> item</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total Harga:</strong>
                        <strong class="text-coffee">Rp <?php echo number_format($total_amount, 0, ',', '.'); ?></strong>
                    </div>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Metode Pembayaran</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="">Pilih metode pembayaran</option>
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="ewallet">E-Wallet</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Catatan untuk pesanan..."></textarea>
                        </div>
                        
                        <button type="submit" name="checkout" class="btn btn-coffee w-100">
                            <i class="bi bi-credit-card"></i> Checkout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
