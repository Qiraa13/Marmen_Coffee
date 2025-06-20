<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Marmen Coffee and Space'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link href="assets\css\custom.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-coffee coffee-icon"></i> <span class="logo-text">Marmen Coffee and Space</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_menu == 'menu' ? 'active' : ''; ?>" href="index.php">Menu</a>
                    </li>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_menu == 'orders' ? 'active' : ''; ?>" href="orders.php">Pesanan Saya</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_menu == 'about' ? 'active' : ''; ?>" href="about.php">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_menu == 'contact' ? 'active' : ''; ?>" href="contact.php">Kontak</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['full_name'] ?? 'Akun'; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php if ($_SESSION['role'] === 'customer'): ?>
                                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="login.php">Login</a></li>
                                <li><a class="dropdown-item" href="register.php">Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="fas fa-shopping-cart"></i> 
                                <span class="badge bg-danger" id="cart-count">0</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
