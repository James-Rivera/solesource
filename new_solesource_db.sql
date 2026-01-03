-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 03, 2026 at 10:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `solesource_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(64) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'COD',
  `status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `phone` varchar(50) DEFAULT NULL,
  `shipping_phone` varchar(20) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Philippines',
  `shipping_address` text DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `courier` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `payment_method`, `status`, `phone`, `shipping_phone`, `full_name`, `address`, `city`, `province`, `region`, `barangay`, `zip_code`, `country`, `shipping_address`, `tracking_number`, `courier`, `created_at`) VALUES
(1, 2, 'SO-20260103042623-1035', 16700.00, 'PayPal', 'pending', '09457996892', NULL, 'James Carlo', 'POLBACION 4 STO TOMAS BATANGAS', 'Angadanan', 'Isabela', NULL, NULL, '4294', 'Philippines', NULL, NULL, NULL, '2026-01-03 03:26:23'),
(2, 2, 'SO-20260103052221-8042', 12000.00, 'COD', 'pending', '09457996892', NULL, 'James Carlo', 'POLBACION 4 STO TOMAS BATANGAS', 'Abulug', 'Cagayan', NULL, NULL, '4294', 'Philippines', NULL, NULL, NULL, '2026-01-03 04:22:21'),
(3, 2, 'SO-20260103052533-5120', 12000.00, 'COD', 'pending', '09457996892', NULL, 'James Carlo', 'POLBACION 4 STO TOMAS BATANGAS', 'Alilem', 'Ilocos Sur', NULL, NULL, '4294', 'Philippines', NULL, NULL, NULL, '2026-01-03 04:25:33'),
(4, 2, 'SO-20260103062551-2575', 12000.00, 'COD', 'pending', '09457996892', NULL, 'James Carlo', 'BLK 27 LOT 25 WINE CUP ST', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', NULL, NULL, '2026-01-03 05:25:51'),
(5, 2, 'SO-20260103064140-5081', 4700.00, 'COD', 'shipped', '09457996892', NULL, 'James Carlo', 'BLK 27 LOT 25 WINE CUP ST', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-5E87B086', 'MockExpress', '2026-01-03 05:41:40'),
(6, 2, 'SO-20260103081635-6473', 4999.00, 'COD', 'delivered', '09457996892', NULL, 'Admin User', 'BLK 27 LOT 25 WINE CUP ST', 'Itbayat', 'Batanes', 'Region II (Cagayan Valley)', 'Raele', '4294', 'Philippines', 'BLK 27 LOT 25 WINE CUP ST, Raele, Itbayat, Batanes, Region II (Cagayan Valley), 4294, Philippines', 'MOCK-C618239C', 'MockExpress', '2026-01-03 07:16:35');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_size_id` int(11) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price_at_purchase` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `size`, `quantity`, `price_at_purchase`, `created_at`) VALUES
(1, 1, 5, '7.5', 1, 12000.00, '2026-01-03 03:26:23'),
(2, 1, 2, '11', 1, 4700.00, '2026-01-03 03:26:23'),
(3, 2, 5, '7', 1, 12000.00, '2026-01-03 04:22:21'),
(4, 3, 5, '11', 1, 12000.00, '2026-01-03 04:25:34'),
(5, 4, 5, '11.5', 1, 12000.00, '2026-01-03 05:25:52'),
(6, 5, 2, '11.5', 1, 4700.00, '2026-01-03 05:41:40'),
(7, 6, 1, '12', 1, 4999.00, '2026-01-03 07:16:35');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `gender` enum('Men','Women','Unisex') DEFAULT 'Unisex',
  `colorway` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `total_sold` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `name`, `brand`, `gender`, `colorway`, `description`, `release_date`, `image`, `price`, `stock_quantity`, `is_featured`, `total_sold`, `status`, `created_at`) VALUES
(1, 'NK-AF1-001', 'AIR FORCE 1', 'Nike', 'Unisex', 'White/White', 'The legend lives on in the Nike Air Force 1.', '2024-01-15', 'assets/img/products/air-force-1.png', 4999.00, 25, 1, 0, 'active', '2026-01-03 03:05:28'),
(2, 'AD-GAZ-IND', 'GAZELLE INDOOR', 'Adidas', 'Unisex', 'Blue Fusion/White', 'Reviving a 1979 classic with premium suede.', '2024-03-10', 'assets/img/products/adidas-gazelle-indoor.jpg', 4700.00, 20, 0, 0, 'active', '2026-01-03 03:05:28'),
(3, 'AS-GK14-001', 'GEL-KAYANO 14', 'Asics', 'Men', 'Cream/Pure Silver', 'Late 2000s aesthetic with retro running shape.', '2024-02-20', 'assets/img/products/asics-gel-kayano-14.png', 9490.00, 15, 0, 0, 'active', '2026-01-03 03:05:28'),
(4, 'PM-SUE-CLS', 'SUEDE CLASSIC', 'Puma', 'Unisex', 'Black/White', 'Game-changing suede icon since 1968.', '2023-11-05', 'assets/img/products/puma-suede-classic.png', 3999.00, 30, 0, 0, 'active', '2026-01-03 03:05:28'),
(5, 'CT8012-116', 'Jordan 11 Retro Legend Blue', 'Jordan', 'Men', 'White/Legend Blue/Black', 'Patent mudguard with Legend Blue hits.', '2024-12-13', 'assets/img/products/jordan-11-legend-blue.png', 12000.00, 8, 1, 0, 'active', '2026-01-03 03:05:28'),
(6, 'AD-SAM-OG', 'SAMBA OG', 'Adidas', 'Unisex', 'Cloud White/Core Black', 'Timeless icon with soft leather upper.', '2024-01-05', 'assets/img/products/adidas-samba-og.png', 5200.00, 18, 0, 0, 'active', '2026-01-03 03:05:28'),
(7, 'AS-GL3-001', 'GEL-LYTE III', 'Asics', 'Men', 'Grey/Black', 'Famous split-tongue runner from the 90s.', '2023-12-15', 'assets/img/products/asics-gel-lyte-iii.png', 7990.00, 12, 0, 0, 'active', '2026-01-03 03:05:28'),
(8, 'PM-RSX-001', 'RS-X', 'Puma', 'Unisex', 'White/Royal/Red', 'Bulky silhouette with retro palette.', '2024-04-01', 'assets/img/products/puma-rsx.png', 5499.00, 16, 0, 0, 'active', '2026-01-03 03:05:28'),
(9, 'NK-DNK-LOW', 'DUNK LOW', 'Nike', 'Unisex', 'Black/White', 'Crisp overlays and original team colors.', '2024-02-28', 'assets/img/products/nike-dunk-low.png', 5795.00, 22, 1, 0, 'active', '2026-01-03 03:05:28'),
(10, 'AD-SUP-STAR', 'SUPERSTAR', 'Adidas', 'Unisex', 'White/Black', 'Shell-toe icon from court to stage.', '2023-10-20', 'assets/img/products/adidas-superstar.png', 4500.00, 28, 0, 0, 'active', '2026-01-03 03:05:28'),
(11, 'AS-GT2-160', 'GT-2160', 'Asics', 'Men', 'White/Illusion Blue', 'GT-2000 series homage with tech language.', '2024-03-25', 'assets/img/products/asics-gt-2160.png', 6890.00, 14, 0, 0, 'active', '2026-01-03 03:05:28'),
(12, 'NK-BLZ-MID', 'BLAZER MID', 'Nike', 'Unisex', 'White/Black', '70s hardwood classic with vintage midsole.', '2023-09-10', 'assets/img/products/nike-blazer-mid.png', 5295.00, 19, 0, 0, 'active', '2026-01-03 03:05:28');

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size_label` varchar(50) NOT NULL,
  `size_system` enum('US','EU','UK','CM') DEFAULT 'US',
  `gender` enum('Men','Women','Unisex') DEFAULT 'Unisex',
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size_label`, `size_system`, `gender`, `stock_quantity`, `is_active`, `created_at`) VALUES
(1, 1, 'Default', 'US', 'Unisex', 25, 1, '2026-01-03 03:05:28'),
(2, 2, 'Default', 'US', 'Unisex', 20, 1, '2026-01-03 03:05:28'),
(3, 3, 'Default', 'US', 'Men', 15, 1, '2026-01-03 03:05:28'),
(4, 4, 'Default', 'US', 'Unisex', 30, 1, '2026-01-03 03:05:28'),
(5, 5, 'Default', 'US', 'Men', 8, 1, '2026-01-03 03:05:28'),
(6, 6, 'Default', 'US', 'Unisex', 18, 1, '2026-01-03 03:05:28'),
(7, 7, 'Default', 'US', 'Men', 12, 1, '2026-01-03 03:05:28'),
(8, 8, 'Default', 'US', 'Unisex', 16, 1, '2026-01-03 03:05:28'),
(9, 9, 'Default', 'US', 'Unisex', 22, 1, '2026-01-03 03:05:28'),
(10, 10, 'Default', 'US', 'Unisex', 28, 1, '2026-01-03 03:05:28'),
(11, 11, 'Default', 'US', 'Men', 14, 1, '2026-01-03 03:05:28'),
(12, 12, 'Default', 'US', 'Unisex', 19, 1, '2026-01-03 03:05:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `birthdate` date DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `birthdate`, `gender`, `is_active`, `created_at`) VALUES
(1, 'Admin User', 'admin@solesource.com', '$2y$10$qANV1OhHZOkNsyAxVeqdzuJIBDgo7Vv3UQCNoArGVXo8y0pV3pAqe', 'admin', NULL, NULL, 1, '2026-01-03 03:06:06'),
(2, 'James Carlo', 'james@solesource.com', '$2y$10$iHmLr9cKN.4P/ptOhCZAC.v/S9ASrMFxpHfFlCM3vnAGunFQouNHy', 'customer', '2005-06-09', 'Prefer not to say', 1, '2026-01-03 03:24:15');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address_line` varchar(255) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Philippines',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `label`, `full_name`, `phone`, `address_line`, `city`, `province`, `region`, `barangay`, `zip_code`, `country`, `is_default`, `created_at`) VALUES
(1, 2, 'Home', 'James Carlo', '09457996892', 'BLK 27 LOT 25 WINE CUP ST', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 0, '2026-01-03 05:10:29'),
(2, 2, 'WORK', 'Admin User', '09457996892', 'BLK 27 LOT 25 WINE CUP ST', 'Itbayat', 'Batanes', 'Region II (Cagayan Valley)', 'Raele', '4294', 'Philippines', 1, '2026-01-03 05:43:20');

-- --------------------------------------------------------

--
-- Table structure for table `user_wishlist`
--

CREATE TABLE `user_wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_wishlist`
--

INSERT INTO `user_wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(2, 2, 5, '2026-01-03 04:18:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_logs_admin` (`admin_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_orders_order_number` (`order_number`),
  ADD KEY `idx_orders_user` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`),
  ADD KEY `idx_order_items_product_size` (`product_size_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_products_sku` (`sku`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_sizes_product` (`product_id`),
  ADD UNIQUE KEY `uq_product_sizes_label` (`product_id`,`size_label`,`gender`,`size_system`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_addresses_user` (`user_id`),
  ADD KEY `idx_user_addresses_default` (`is_default`);

--
-- Indexes for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_product` (`user_id`,`product_id`),
  ADD KEY `fk_wishlist_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `fk_admin_logs_user` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product_size` FOREIGN KEY (`product_size_id`) REFERENCES `product_sizes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `fk_product_sizes_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `fk_user_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
