-- Menambahkan data sample untuk testing
USE coffeeshop_db;

-- Tambah kategori sample
INSERT INTO categories (name, description) VALUES
('Pastry', 'Kue dan roti segar'),
('Beverages', 'Minuman non-kopi'),
('Desserts', 'Makanan penutup');

-- Tambah produk sample
INSERT INTO products (name, description, price, stock, category_id, image) VALUES
('Latte', 'Kopi latte dengan foam art', 28000, 25, 1, 'latte.jpg'),
('Americano', 'Kopi hitam klasik', 18000, 35, 1, 'americano.jpg'),
('Frappuccino', 'Kopi dingin dengan whipped cream', 35000, 20, 2, 'frappuccino.jpg'),
('Green Tea Latte', 'Latte dengan matcha premium', 32000, 15, 2, 'green_tea_latte.jpg'),
('Chocolate Cake', 'Kue coklat lembut dengan ganache', 25000, 10, 4, 'chocolate_cake.jpg'),
('Cheesecake', 'Kue keju New York style', 28000, 8, 4, 'cheesecake.jpg'),
('Bagel', 'Roti bagel dengan cream cheese', 15000, 12, 1, 'bagel.jpg'),
('Muffin Blueberry', 'Muffin dengan blueberry segar', 18000, 15, 1, 'muffin.jpg');

-- Tambah user sample
INSERT INTO users (username, email, password, full_name, role, phone, address) VALUES
('sarah123', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Johnson', 'customer', '081234567892', 'Jl. Sudirman No. 45, Jakarta'),
('mike_admin', 'mike@marmencoffee.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael Anderson', 'admin', '081234567893', 'Jl. Thamrin No. 12, Jakarta'),
('lisa_customer', 'lisa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lisa Wong', 'customer', '081234567894', 'Jl. Gatot Subroto No. 78, Jakarta'),
('david_user', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Smith', 'customer', '081234567895', 'Jl. Kuningan No. 23, Jakarta');

-- Tambah sample orders
INSERT INTO orders (user_id, total_amount, status, payment_status, payment_method, notes) VALUES
(2, 46000, 'completed', 'paid', 'cash', 'Tanpa gula tambahan'),
(4, 63000, 'preparing', 'paid', 'transfer', 'Extra hot please'),
(6, 28000, 'pending', 'pending', 'ewallet', '');

-- Tambah sample order items
INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES
(1, 1, 1, 15000, 15000),
(1, 2, 1, 25000, 25000),
(1, 8, 1, 12000, 12000),
(2, 3, 1, 20000, 20000),
(2, 5, 1, 25000, 25000),
(2, 9, 1, 18000, 18000),
(3, 2, 1, 28000, 28000);
