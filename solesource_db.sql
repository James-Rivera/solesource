-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 02, 2026 at 10:05 AM
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

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(64) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'COD',
  `status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `phone` varchar(50) DEFAULT NULL,
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
  `courier` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(10) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
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
  `sku` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `gender` enum('Men','Women','Unisex') DEFAULT 'Unisex',
  `colorway` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `total_sold` int(11) DEFAULT 0,
  `status` enum('active','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `name`, `brand`, `gender`, `colorway`, `description`, `release_date`, `image`, `price`, `stock_quantity`, `is_featured`, `total_sold`, `status`, `created_at`) VALUES
(13, 'NK-AF1-001', 'AIR FORCE 1', 'Nike', 'Unisex', 'White/White', 'The legend lives on in the Nike Air Force 1, a modern take on the iconic sneaker that blends classic style with fresh, crisp details.', '2024-01-15', 'assets/img/products/air-force-1.png', 4999.00, 10, 0, 0, 'active', '2026-01-02 09:02:07'),
(14, 'AD-GAZ-IND', 'GAZELLE INDOOR', 'Adidas', 'Unisex', 'Blue Fusion/White', 'Reviving a 1979 classic, the Gazelle Indoor features a translucent gum rubber outsole and premium suede upper.', '2024-03-10', 'assets/img/products/adidas-gazelle-indoor.jpg', 4700.00, 10, 0, 0, 'active', '2026-01-02 09:02:07'),
(15, 'AS-GK14-001', 'GEL-KAYANO 14', 'Asics', 'Men', 'Cream/Pure Silver', 'Resurfacing with its late 2000s aesthetic, the GEL-KAYANO 14 conveys a new perception of retro running shapes.', '2024-02-20', 'assets/img/products/asics-gel-kayano-14.png', 9490.00, 10, 0, 0, 'active', '2026-01-02 09:02:07'),
(16, 'PM-SUE-CLS', 'SUEDE CLASSIC', 'Puma', 'Unisex', 'Black/White', 'The Suede has been changing the game ever since 1968. It has been worn by icons of every generation.', '2023-11-05', 'assets/img/products/air-force-1.png', 3999.00, 10, 0, 0, 'active', '2026-01-02 09:02:07'),
(17, 'CT8012-116', 'Jordan 11 Retro Legend Blue', 'Jordan', 'Men', 'White/Legend Blue/Black', 'Originally released in 1996, the 2024 edition sports a white leather upper, accented with a matching patent leather mudguard.', '2024-12-13', 'assets/img/products/jordan-11-legend-blue.png', 12000.00, 10, 0, 0, 'active', '2026-01-02 09:02:07'),
(18, 'AD-SAM-OG', 'SAMBA OG', 'Adidas', 'Unisex', 'Cloud White/Core Black', 'Born on the pitch, the Samba is a timeless icon of street style. This version stays true to the legacy with a soft leather upper.', '2024-01-05', 'assets/img/products/air-force-1.png', 5200.00, 10, 0, 0, 'active', '2026-01-02 09:02:07'),
(19, 'AS-GL3-001', 'GEL-LYTE III', 'Asics', 'Men', 'Grey/Black', 'Famous for its split-tongue design, the GEL-LYTE III OG sneaker re-emerges with its original shape and tooling from the early 1990s.', '2023-12-15', 'assets/img/products/air-force-1.png', 7990.00, 10, 0, 0, 'active', '2026-01-02 09:02:07'),
(20, 'PM-RSX-001', 'RS-X', 'Puma', 'Unisex', 'White/Royal/Red', 'The RS-X returned to style with a bulky silhouette and retro color palette, mixing mesh and leather for a futuristic look.', '2024-04-01', 'assets/img/products/air-force-1.png', 5499.00, 10, 0, 0, 'active', '2026-01-02 09:02:07'),
(21, 'NK-DNK-LOW', 'DUNK LOW', 'Nike', 'Unisex', 'Black/White', 'Created for the hardwood but taken to the streets, the Nike Dunk Low returns with crisp overlays and original team colors.', '2024-02-28', 'assets/img/products/air-force-1.png', 5795.00, 10, 0, 0, 'active', '2026-01-02 09:02:07'),
(22, 'AD-SUP-STAR', 'SUPERSTAR', 'Adidas', 'Unisex', 'White/Black', 'From the basketball court to the hip hop stage, the adidas Superstar has cemented its status as an icon with its famous shell toe.', '2023-10-20', 'assets/img/products/air-force-1.png', 4500.00, 10, 0, 0, 'active', '2026-01-02 09:02:07'),
(23, 'AS-GT2-160', 'GT-2160', 'Asics', 'Men', 'White/Illusion Blue', 'The GT-2160 sneaker pays homage to the technical design language from the GT-2000 series in the early 2010s.', '2024-03-25', 'assets/img/products/air-force-1.png', 6890.00, 10, 0, 0, 'active', '2026-01-02 09:02:07'),
(24, 'NK-BLZ-MID', 'BLAZER MID', 'Nike', 'Unisex', 'White/Black', 'In the 70s, Nike was the new shoe on the block. The Blazer Mid is a blast from the past with vintage treatment on the midsole.', '2023-09-10', 'assets/img/products/air-force-1.png', 5295.00, 10, 0, 0, 'active', '2026-01-02 09:02:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_default` (`is_default`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

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
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
