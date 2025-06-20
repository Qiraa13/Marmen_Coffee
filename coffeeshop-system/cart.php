<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireCustomer();

$page_title = "Keranjang Belanja - Marmen Coffee and Space";
$active_menu = "cart";
include 'includes/header.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h2>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div id="cart-items">
                        <!-- Cart items will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Ringkasan Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span>Total Item:</span>
                        <span id="total-items">0</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Total Harga:</span>
                        <span id="total-price">Rp 0</span>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="payment-method" class="form-label">Metode Pembayaran</label>
                        <select class="form-select" id="payment-method">
                            <option value="cash">Tunai</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="ewallet">E-Wallet</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="notes" rows="3" placeholder="Catatan tambahan..."></textarea>
                    </div>
                    <button class="btn btn-success w-100" id="checkout-btn" disabled>
                        <i class="fas fa-credit-card"></i> Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    function formatPrice(price) {
        return 'Rp ' + price.toLocaleString('id-ID');
    }
    
    function updateCart() {
        const cartItemsContainer = document.getElementById('cart-items');
        const totalItems = document.getElementById('total-items');
        const totalPrice = document.getElementById('total-price');
        const checkoutBtn = document.getElementById('checkout-btn');
        
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted"></i>
                    <h4 class="mt-3 text-muted">Keranjang Kosong</h4>
                    <p class="text-muted">Silakan tambahkan produk ke keranjang</p>
                    <a href="index.php" class="btn btn-primary">Lihat Menu</a>
                </div>
            `;
            checkoutBtn.disabled = true;
            totalItems.textContent = '0';
            totalPrice.textContent = 'Rp 0';
            
            // Update navbar cart count
            updateNavbarCartCount();
            return;
        }
        
        let html = '';
        let total = 0;
        let itemCount = 0;
        
        cart.forEach((item, index) => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            itemCount += item.quantity;
            
            html += `
                <div class="d-flex align-items-center border-bottom py-3">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${item.name}</h6>
                        <p class="mb-1 text-muted">${formatPrice(item.price)}</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="mx-3">${item.quantity}</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger ms-3" onclick="removeItem(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="ms-3">
                        <strong>${formatPrice(subtotal)}</strong>
                    </div>
                </div>
            `;
        });
        
        cartItemsContainer.innerHTML = html;
        totalItems.textContent = itemCount;
        totalPrice.textContent = formatPrice(total);
        checkoutBtn.disabled = false;
        
        // Update navbar cart count
        updateNavbarCartCount();
    }
    
    function updateQuantity(index, change) {
        cart[index].quantity += change;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCart();
    }
    
    function removeItem(index) {
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCart();
    }
    
    function updateNavbarCartCount() {
        const cartElement = document.getElementById('cart-count');
        if (cartElement) {
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartElement.textContent = totalItems;
        }
    }
    
    document.getElementById('checkout-btn').addEventListener('click', function() {
        const paymentMethod = document.getElementById('payment-method').value;
        const notes = document.getElementById('notes').value;
        
        // Send order to server
        fetch('process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart: cart,
                payment_method: paymentMethod,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                localStorage.removeItem('cart');
                alert('Pesanan berhasil dibuat!');
                window.location.href = 'orders.php';
            } else {
                alert('Terjadi kesalahan: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memproses pesanan');
        });
    });
    
    // Initialize cart display
    updateCart();
</script>

<?php include 'includes/footer.php'; ?>
