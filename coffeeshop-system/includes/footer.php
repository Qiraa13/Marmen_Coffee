<footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="logo-text"><i class="fas fa-coffee coffee-icon"></i> Marmen Coffee and Space</h5>
                    <p>Tempat terbaik untuk menikmati kopi berkualitas dan suasana yang nyaman.</p>
                </div>
                <div class="col-md-4">
                    <h5>Jam Buka</h5>
                    <p>Senin - Jumat: 08:00 - 22:00<br>
                    Sabtu - Minggu: 10:00 - 23:00</p>
                </div>
                <div class="col-md-4">
                    <h5>Kontak</h5>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Tamin No. 1, Bandar Lampung<br>
                    <i class="fas fa-phone"></i> +62 812 3456 7890<br>
                    <i class="fas fa-envelope"></i> info@marmencoffee.com</p>
                </div>
            </div>
            <div class="text-center mt-3">
                <p>&copy; <?php echo date('Y'); ?> Marmen Coffee and Space. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update cart count on page load
        function updateCartCount() {
            const cartElement = document.getElementById('cart-count');
            if (cartElement) {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                cartElement.textContent = totalItems;
            }
        }
        
        // Initialize cart count when page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
        
        // Update cart count when storage changes (for multiple tabs)
        window.addEventListener('storage', function(e) {
            if (e.key === 'cart') {
                updateCartCount();
            }
        });
    </script>
</body>
</html>
