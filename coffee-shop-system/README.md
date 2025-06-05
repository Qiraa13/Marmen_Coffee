# Coffee Shop Management System

Sistem manajemen coffee shop dengan 2 role (Customer & Admin) menggunakan PHP, MySQL, dan Bootstrap.

## Fitur

### Customer
- Register & Login
- Browse menu dengan filter kategori
- Tambah produk ke keranjang
- Checkout dan pembayaran
- Lihat riwayat pesanan
- Update profile

### Admin
- Dashboard dengan statistik
- Kelola produk (CRUD)
- Kelola pesanan & verifikasi
- Kelola user
- Monitor stok
- Kelola kategori produk
- Halaman 404 error yang user-friendly

## Instalasi

1. **Database Setup**
   - Import file `database/coffee_shop.sql` ke MySQL
   - Sesuaikan konfigurasi database di `config/database.php`

2. **Web Server**
   - Upload semua file ke web server (Apache/Nginx)
   - Pastikan PHP 7.4+ dan MySQL 5.7+ terinstall

3. **Login Admin**
   - Username: `admin`
   - Password: `password`

## Struktur Folder

\`\`\`
coffee_shop/
├── 404.php               # Error page
├── admin/
│   ├── categories.php    # Category management
│   ├── dashboard.php     # Admin dashboard
│   ├── orders.php        # Order management
│   ├── products.php      # Product management
│   └── users.php         # User management
├── assets/             # CSS, JS, images
├── config/             # Database configuration
├── database/           # SQL files
├── includes/           # Header, footer, auth
├── index.php           # Homepage
├── login.php           # Login page
├── register.php        # Register page
├── menu.php            # Product menu
├── cart.php            # Shopping cart
├── orders.php          # Order history
├── profile.php         # User profile
└── README.md           # Documentation
\`\`\`

## Teknologi

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap 5.3, HTML5, CSS3, JavaScript
- **Icons**: Bootstrap Icons

## Keamanan

- Password hashing dengan PHP password_hash()
- Session management
- Role-based access control
- SQL injection protection dengan prepared statements
- XSS protection dengan htmlspecialchars()

## Pengembangan Lanjutan

- Upload gambar produk
- Integrasi payment gateway
- Notifikasi real-time
- Laporan penjualan
- API untuk mobile app
- Multi-language support
- Error logging dan monitoring
- Advanced search dan filtering
- Bulk operations untuk admin

## Lisensi

MIT License
