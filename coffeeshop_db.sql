-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 10, 2025 at 02:11 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `coffeeshop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Hot Coffee', 'Minuman kopi panas', '2025-06-09 14:25:49'),
(2, 'Cold Coffee', 'Minuman kopi dingin', '2025-06-09 14:25:49'),
(3, 'Non Coffee', 'Minuman non kopi', '2025-06-09 14:25:49'),
(4, 'Snacks', 'Makanan ringan', '2025-06-09 14:25:49'),
(5, 'Pastry', 'Kue dan roti segar', '2025-06-09 15:10:40'),
(7, 'Desserts', 'Makanan penutup', '2025-06-09 15:10:40'),
(8, 'Coffee', 'Minuman Kopi', '2025-06-09 15:10:40');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','preparing','ready','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `payment_method` enum('cash','transfer','ewallet') DEFAULT 'cash',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `payment_status`, `payment_method`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 25000.00, 'cancelled', 'pending', 'cash', '', '2025-06-09 14:36:34', '2025-06-09 15:15:07'),
(2, 2, 46000.00, 'completed', 'paid', 'cash', 'Tanpa gula tambahan', '2025-06-09 15:10:41', '2025-06-09 15:10:41'),
(3, 4, 63000.00, 'preparing', 'paid', 'transfer', 'Extra hot please', '2025-06-09 15:10:41', '2025-06-09 15:10:41'),
(4, 6, 28000.00, 'pending', 'pending', 'ewallet', '', '2025-06-09 15:10:41', '2025-06-09 15:10:41');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 2, 1, 25000.00, 25000.00),
(2, 1, 1, 1, 15000.00, 15000.00),
(3, 1, 2, 1, 25000.00, 25000.00),
(4, 1, 8, 1, 12000.00, 12000.00),
(5, 2, 3, 1, 20000.00, 20000.00),
(6, 2, 5, 1, 25000.00, 25000.00),
(7, 2, 9, 1, 18000.00, 18000.00),
(8, 3, 2, 1, 28000.00, 28000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock` int DEFAULT '0',
  `category_id` int DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `category_id`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Espresso', 'Kopi espresso klasik', 15000.00, 51, 1, '6846f6156d688.jpg', 'active', '2025-06-09 14:25:49', '2025-06-09 15:15:07'),
(2, 'Cappuccino', 'Kopi dengan foam susu', 25000.00, 31, 1, 'cappuccino.jpg', 'active', '2025-06-09 14:25:49', '2025-06-09 15:15:07'),
(3, 'Iced Coffee', 'Kopi dingin dengan es', 20000.00, 40, 2, 'iced_coffee.jpg', 'active', '2025-06-09 14:25:49', '2025-06-09 14:25:49'),
(4, 'Croissant', 'Roti croissant segar', 12000.00, 20, 5, 'croissant.jpg', 'active', '2025-06-09 14:25:49', '2025-06-10 01:58:59'),
(5, 'Latte', 'Kopi latte dengan foam art', 28000.00, 25, 8, 'latte.jpg', 'active', '2025-06-09 15:10:40', '2025-06-09 15:18:20'),
(6, 'Americano', 'Kopi hitam klasik', 18000.00, 35, 8, 'americano.jpg', 'active', '2025-06-09 15:10:40', '2025-06-09 15:18:29'),
(7, 'Frappuccino', 'Kopi dingin dengan whipped cream', 35000.00, 20, 2, 'frappuccino.jpg', 'active', '2025-06-09 15:10:40', '2025-06-09 15:10:40'),
(8, 'Green Tea Latte', 'Latte dengan matcha premium', 32000.00, 16, 3, 'green_tea_latte.jpg', 'active', '2025-06-09 15:10:40', '2025-06-09 15:18:37'),
(9, 'Chocolate Cake', 'Kue coklat lembut dengan ganache', 25000.00, 10, 7, 'chocolate_cake.jpg', 'active', '2025-06-09 15:10:40', '2025-06-10 01:57:52'),
(10, 'Cheesecake', 'Kue keju New York style', 28000.00, 8, 7, 'cheesecake.jpg', 'active', '2025-06-09 15:10:40', '2025-06-10 01:58:00'),
(11, 'Bagel', 'Roti bagel dengan cream cheese', 15000.00, 12, 5, 'bagel.jpg', 'active', '2025-06-09 15:10:40', '2025-06-10 01:58:41'),
(12, 'Muffin Blueberry', 'Muffin dengan blueberry segar', 18000.00, 15, 4, 'muffin.jpg', 'active', '2025-06-09 15:10:40', '2025-06-10 01:58:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@coffeeshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', '081234567890', NULL, '2025-06-09 14:25:49', '2025-06-09 14:25:49'),
(2, 'customer1', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'customer', '081234567891', NULL, '2025-06-09 14:25:49', '2025-06-09 14:25:49'),
(3, 'sarah123', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Johnson', 'customer', '081234567892', 'Jl. Sudirman No. 45, Jakarta', '2025-06-09 15:10:40', '2025-06-09 15:10:40'),
(4, 'mike_admin', 'mike@marmencoffee.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michael Anderson', 'admin', '081234567893', 'Jl. Thamrin No. 12, Jakarta', '2025-06-09 15:10:40', '2025-06-09 15:10:40'),
(5, 'lisa_customer', 'lisa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lisa Wong', 'customer', '081234567894', 'Jl. Gatot Subroto No. 78, Jakarta', '2025-06-09 15:10:40', '2025-06-09 15:10:40'),
(6, 'david_user', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Smith', 'customer', '081234567895', 'Jl. Kuningan No. 23, Jakarta', '2025-06-09 15:10:40', '2025-06-09 15:10:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
