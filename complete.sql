-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 03, 2026 at 06:03 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
(1, 2, 'SO-20260103162005-8100', 9490.00, 'PayPal', 'shipped', '09457996892', NULL, 'James Carlo', 'BLK 27 LOT 25 WINE CUP ST', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-4840298B', 'MockExpress', '2026-01-03 15:20:05');

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

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_size_id`, `size`, `quantity`, `price_at_purchase`, `created_at`) VALUES
(1, 1, 3, 15, 'US 10', 1, 9490.00, '2026-01-03 15:20:05');

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
  `gender` enum('Men','Women') NOT NULL,
  `colorway` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `total_sold` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sport` enum('Running','Training','Lifestyle','Basketball') DEFAULT NULL,
  `secondary_gender` enum('Men','Women','None') NOT NULL DEFAULT 'None'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `name`, `brand`, `gender`, `colorway`, `description`, `release_date`, `image`, `price`, `stock_quantity`, `is_featured`, `total_sold`, `status`, `created_at`, `sport`, `secondary_gender`) VALUES
(1, 'NK-AF1-001', 'AIR FORCE 1', 'Nike', 'Men', 'White/White', 'Legendary leather icon with everyday cushioning.', '2024-01-15', 'assets/img/products/air-force-1.png', 4999.00, 27, 1, 0, 'active', '2026-01-03 14:28:54', 'Lifestyle', 'None'),
(2, 'AD-GAZ-IND', 'GAZELLE INDOOR', 'Adidas', 'Men', 'Blue Fusion/White', '1979 indoor classic with soft suede and gum tooling.', '2024-03-10', 'assets/img/products/adidas-gazelle-indoor.jpg', 4700.00, 18, 0, 0, 'active', '2026-01-03 14:28:54', 'Lifestyle', 'None'),
(3, 'AS-GK14-001', 'GEL-KAYANO 14', 'Asics', 'Men', 'Cream/Pure Silver', 'Retro runner revived with GEL cushioning.', '2024-02-20', 'assets/img/products/asics-gel-kayano-14.png', 9490.00, 12, 0, 0, 'active', '2026-01-03 14:28:54', 'Running', 'Women'),
(4, 'PM-SUE-CLS', 'SUEDE CLASSIC', 'Puma', 'Men', 'Black/White', 'Street staple since 1968 with soft suede upper.', '2023-11-05', 'assets/img/products/puma-suede-classic.png', 3999.00, 21, 0, 0, 'active', '2026-01-03 14:28:54', 'Lifestyle', 'None'),
(5, 'CT8012-116', 'Jordan 11 Retro Legend Blue', 'Jordan', 'Men', 'White/Legend Blue/Black', 'Patent mudguard shine with icy outsole.', '2024-12-13', 'assets/img/products/jordan-11-legend-blue.png', 12000.00, 8, 1, 0, 'active', '2026-01-03 14:28:54', 'Basketball', 'None'),
(6, 'AD-SAM-OG', 'SAMBA OG', 'Adidas', 'Men', 'Cloud White/Core Black', 'Timeless indoor silhouette with suede toe cap.', '2024-01-05', 'assets/img/products/adidas-samba-og.png', 5200.00, 18, 0, 0, 'active', '2026-01-03 14:28:54', 'Lifestyle', 'None'),
(7, 'AS-GL3-001', 'GEL-LYTE III', 'Asics', 'Men', 'Grey/Black', '90s split-tongue icon with cushioned ride.', '2023-12-15', 'assets/img/products/asics-gel-lyte-iii.png', 7990.00, 3, 0, 0, 'active', '2026-01-03 14:28:54', 'Lifestyle', 'None'),
(8, 'NK-DNK-LOW', 'DUNK LOW', 'Nike', 'Men', 'Black/White', 'Crisp overlays and heritage hoops DNA.', '2024-02-28', 'assets/img/products/nike-dunk-low.png', 5795.00, 18, 1, 0, 'active', '2026-01-03 14:28:54', 'Basketball', 'None'),
(9, 'AD-SUP-STAR', 'SUPERSTAR', 'Adidas', 'Men', 'White/Black', 'Shell-toe legend from court to stage.', '2023-10-20', 'assets/img/products/adidas-superstar.png', 4500.00, 20, 0, 0, 'active', '2026-01-03 14:28:54', 'Lifestyle', 'None'),
(10, 'AS-GT2-160', 'GT-2160', 'Asics', 'Men', 'White/Illusion Blue', 'GT-2000 lineage with modern tooling.', '2024-03-25', 'assets/img/products/asics-gt-2160.png', 6890.00, 3, 0, 0, 'active', '2026-01-03 14:28:54', 'Running', 'None'),
(11, 'NK-BLZ-MID', 'BLAZER MID', 'Nike', 'Men', 'White/Black', '70s hardwood staple with vintage foxing.', '2023-09-10', 'assets/img/products/nike-blazer-mid.png', 5295.00, 16, 0, 0, 'active', '2026-01-03 14:28:54', 'Basketball', 'None'),
(12, 'NK-PEG-41-W', 'AIR ZOOM PEGASUS 41 W', 'Nike', 'Women', 'Photon Dust/Volt', 'Daily trainer with ReactX foam for lively miles.', '2024-06-01', 'assets/img/products/nike-pegasus-41-w.png', 6795.00, 11, 0, 0, 'active', '2026-01-03 14:28:54', 'Running', 'None'),
(13, 'NK-MTC-9-W', 'METCON 9 W', 'Nike', 'Women', 'Black/Anthracite', 'Stable platform with rope-guard wrap for lifts and WODs.', '2024-05-15', 'assets/img/products/nike-metcon-9-w.png', 8200.00, 8, 0, 0, 'active', '2026-01-03 14:28:54', 'Training', 'None'),
(14, 'AD-UB-LGT-W', 'ULTRABOOST LIGHT W', 'Adidas', 'Women', 'Halo Blue/White', 'Max-cushioned trainer with Light BOOST midsole.', '2024-04-12', 'assets/img/products/adidas-ultraboost-light-w.png', 10500.00, 9, 1, 0, 'active', '2026-01-03 14:28:54', 'Running', 'None'),
(15, 'AD-ASTIR-W', 'ASTIR W', 'Adidas', 'Women', 'Silver Dawn/Black', 'Chunky lifestyle runner with playful overlays.', '2024-02-05', 'assets/img/products/adidas-astir-w.png', 5500.00, 10, 0, 0, 'active', '2026-01-03 14:28:54', 'Lifestyle', 'None'),
(16, 'AS-GT2000-12W', 'GT-2000 12 W', 'Asics', 'Women', 'White/Light Sage', 'Stability trainer with 3D Guidance System.', '2024-03-08', 'assets/img/products/asics-gt-2000-12-w.png', 7600.00, 8, 0, 0, 'active', '2026-01-03 14:28:54', 'Running', 'None'),
(17, 'AS-NOVA-3W', 'NOVABLAST 3 W', 'Asics', 'Women', 'Mint Tint/White', 'Bouncy FF BLAST PLUS foam for daily tempo.', '2024-01-25', 'assets/img/products/asics-novablast-3-w.png', 8500.00, 7, 1, 0, 'active', '2026-01-03 14:28:54', 'Running', 'None'),
(18, 'NK-SAB-1-W', 'SABRINA 1 W', 'Nike', 'Women', 'Oxygen Purple/Black', 'Lightweight guard shoe tuned for quick cuts.', '2024-07-18', 'assets/img/products/nike-sabrina-1-w.png', 8900.00, 6, 0, 0, 'active', '2026-01-03 14:28:54', 'Basketball', 'None');

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size_label` varchar(50) NOT NULL,
  `size_system` enum('US','EU','UK','CM') DEFAULT 'US',
  `gender` enum('Men','Women','Both') NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size_label`, `size_system`, `gender`, `stock_quantity`, `is_active`, `created_at`) VALUES
(1, 1, 'US M 7', 'US', 'Men', 5, 1, '2026-01-03 14:28:54'),
(2, 1, 'US M 8', 'US', 'Men', 8, 1, '2026-01-03 14:28:54'),
(3, 1, 'US M 9', 'US', 'Men', 6, 1, '2026-01-03 14:28:54'),
(4, 1, 'US M 10', 'US', 'Men', 4, 1, '2026-01-03 14:28:54'),
(7, 2, 'US 6', 'US', 'Men', 4, 1, '2026-01-03 14:28:54'),
(8, 2, 'US 7', 'US', 'Men', 6, 1, '2026-01-03 14:28:54'),
(9, 2, 'US 8', 'US', 'Men', 5, 1, '2026-01-03 14:28:54'),
(10, 2, 'US 9', 'US', 'Men', 3, 1, '2026-01-03 14:28:54'),
(15, 3, 'US 10', 'US', 'Men', 1, 1, '2026-01-03 14:28:54'),
(16, 3, 'US 11', 'US', 'Men', 0, 1, '2026-01-03 14:28:54'),
(17, 4, 'US 7', 'US', 'Men', 6, 1, '2026-01-03 14:28:54'),
(18, 4, 'US 8', 'US', 'Men', 7, 1, '2026-01-03 14:28:54'),
(19, 4, 'US 9', 'US', 'Men', 5, 1, '2026-01-03 14:28:54'),
(20, 4, 'US 10', 'US', 'Men', 3, 1, '2026-01-03 14:28:54'),
(22, 5, 'US 9', 'US', 'Men', 2, 1, '2026-01-03 14:28:54'),
(23, 5, 'US 10', 'US', 'Men', 3, 1, '2026-01-03 14:28:54'),
(24, 5, 'US 11', 'US', 'Men', 3, 1, '2026-01-03 14:28:54'),
(25, 5, 'US 12', 'US', 'Men', 0, 1, '2026-01-03 14:28:54'),
(27, 6, 'US 6', 'US', 'Men', 4, 1, '2026-01-03 14:28:54'),
(28, 6, 'US 7', 'US', 'Men', 5, 1, '2026-01-03 14:28:54'),
(29, 6, 'US 8', 'US', 'Men', 6, 1, '2026-01-03 14:28:54'),
(30, 6, 'US 9', 'US', 'Men', 3, 1, '2026-01-03 14:28:54'),
(35, 7, 'US 9', 'US', 'Men', 2, 1, '2026-01-03 14:28:54'),
(36, 7, 'US 10', 'US', 'Men', 1, 1, '2026-01-03 14:28:54'),
(37, 8, 'US 6', 'US', 'Men', 4, 1, '2026-01-03 14:28:54'),
(38, 8, 'US 7', 'US', 'Men', 5, 1, '2026-01-03 14:28:54'),
(39, 8, 'US 8', 'US', 'Men', 4, 1, '2026-01-03 14:28:54'),
(40, 8, 'US 9', 'US', 'Men', 5, 1, '2026-01-03 14:28:54'),
(42, 9, 'US 6', 'US', 'Men', 5, 1, '2026-01-03 14:28:54'),
(43, 9, 'US 7', 'US', 'Men', 6, 1, '2026-01-03 14:28:54'),
(44, 9, 'US 8', 'US', 'Men', 5, 1, '2026-01-03 14:28:54'),
(45, 9, 'US 9', 'US', 'Men', 4, 1, '2026-01-03 14:28:54'),
(50, 10, 'US 9', 'US', 'Men', 2, 1, '2026-01-03 14:28:54'),
(51, 10, 'US 10', 'US', 'Men', 1, 1, '2026-01-03 14:28:54'),
(52, 11, 'US 7', 'US', 'Men', 4, 1, '2026-01-03 14:28:54'),
(53, 11, 'US 8', 'US', 'Men', 5, 1, '2026-01-03 14:28:54'),
(54, 11, 'US 9', 'US', 'Men', 4, 1, '2026-01-03 14:28:54'),
(55, 11, 'US 10', 'US', 'Men', 3, 1, '2026-01-03 14:28:54'),
(57, 12, 'US W 6', 'US', 'Women', 3, 1, '2026-01-03 14:28:54'),
(58, 12, 'US W 7', 'US', 'Women', 4, 1, '2026-01-03 14:28:54'),
(59, 12, 'US W 8', 'US', 'Women', 4, 1, '2026-01-03 14:28:54'),
(62, 13, 'US W 6.5', 'US', 'Women', 2, 1, '2026-01-03 14:28:54'),
(63, 13, 'US W 7.5', 'US', 'Women', 3, 1, '2026-01-03 14:28:54'),
(64, 13, 'US W 8.5', 'US', 'Women', 3, 1, '2026-01-03 14:28:54'),
(68, 14, 'US W 6', 'US', 'Women', 2, 1, '2026-01-03 14:28:54'),
(69, 14, 'US W 7', 'US', 'Women', 2, 1, '2026-01-03 14:28:54'),
(70, 14, 'US W 8', 'US', 'Women', 3, 1, '2026-01-03 14:28:54'),
(71, 14, 'US W 9', 'US', 'Women', 2, 1, '2026-01-03 14:28:54'),
(74, 15, 'US W 6', 'US', 'Women', 3, 1, '2026-01-03 14:28:54'),
(75, 15, 'US W 7', 'US', 'Women', 4, 1, '2026-01-03 14:28:54'),
(76, 15, 'US W 8', 'US', 'Women', 3, 1, '2026-01-03 14:28:54'),
(79, 16, 'US W 6', 'US', 'Women', 2, 1, '2026-01-03 14:28:54'),
(80, 16, 'US W 7', 'US', 'Women', 3, 1, '2026-01-03 14:28:54'),
(81, 16, 'US W 8', 'US', 'Women', 3, 1, '2026-01-03 14:28:54'),
(84, 17, 'US W 6', 'US', 'Women', 2, 1, '2026-01-03 14:28:54'),
(85, 17, 'US W 7', 'US', 'Women', 2, 1, '2026-01-03 14:28:54'),
(86, 17, 'US W 8', 'US', 'Women', 3, 1, '2026-01-03 14:28:54'),
(90, 18, 'US W 6.5', 'US', 'Women', 2, 1, '2026-01-03 14:28:54'),
(91, 18, 'US W 7.5', 'US', 'Women', 2, 1, '2026-01-03 14:28:54'),
(92, 18, 'US W 8.5', 'US', 'Women', 2, 1, '2026-01-03 14:28:54'),
(95, 12, 'US W 5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(96, 12, 'US W 5.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(97, 12, 'US W 6.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(98, 12, 'US W 7.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(99, 12, 'US W 8.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(100, 12, 'US W 9', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(101, 12, 'US W 9.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(102, 12, 'US W 10', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(103, 12, 'US W 10.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(104, 12, 'US W 11', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(105, 12, 'US W 11.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(106, 12, 'US W 12', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(107, 13, 'US W 5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(108, 13, 'US W 5.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(109, 13, 'US W 6', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(110, 13, 'US W 7', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(111, 13, 'US W 8', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(112, 13, 'US W 9', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(113, 13, 'US W 9.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(114, 13, 'US W 10', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(115, 13, 'US W 10.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(116, 13, 'US W 11', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(117, 13, 'US W 11.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(118, 13, 'US W 12', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(119, 14, 'US W 5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(120, 14, 'US W 5.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(121, 14, 'US W 6.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(122, 14, 'US W 7.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(123, 14, 'US W 8.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(124, 14, 'US W 9.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(125, 14, 'US W 10', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(126, 14, 'US W 10.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(127, 14, 'US W 11', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(128, 14, 'US W 11.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(129, 14, 'US W 12', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(130, 15, 'US W 5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(131, 15, 'US W 5.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(132, 15, 'US W 6.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(133, 15, 'US W 7.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(134, 15, 'US W 8.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(135, 15, 'US W 9', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(136, 15, 'US W 9.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(137, 15, 'US W 10', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(138, 15, 'US W 10.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(139, 15, 'US W 11', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(140, 15, 'US W 11.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(141, 15, 'US W 12', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(142, 16, 'US W 5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(143, 16, 'US W 5.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(144, 16, 'US W 6.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(145, 16, 'US W 7.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(146, 16, 'US W 8.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(147, 16, 'US W 9', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(148, 16, 'US W 9.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(149, 16, 'US W 10', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(150, 16, 'US W 10.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(151, 16, 'US W 11', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(152, 16, 'US W 11.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(153, 16, 'US W 12', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(154, 17, 'US W 5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(155, 17, 'US W 5.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(156, 17, 'US W 6.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(157, 17, 'US W 7.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(158, 17, 'US W 8.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(159, 17, 'US W 9', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(160, 17, 'US W 9.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(161, 17, 'US W 10', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(162, 17, 'US W 10.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(163, 17, 'US W 11', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(164, 17, 'US W 11.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(165, 17, 'US W 12', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(166, 18, 'US W 5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(167, 18, 'US W 5.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(168, 18, 'US W 6', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(169, 18, 'US W 7', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(170, 18, 'US W 8', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(171, 18, 'US W 9', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(172, 18, 'US W 9.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(173, 18, 'US W 10', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(174, 18, 'US W 10.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(175, 18, 'US W 11', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(176, 18, 'US W 11.5', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(177, 18, 'US W 12', 'US', 'Women', 0, 1, '2026-01-03 15:18:53'),
(222, 1, 'US 6', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(223, 1, 'US 6.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(224, 1, 'US 7', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(225, 1, 'US 7.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(226, 1, 'US 8', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(227, 1, 'US 8.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(228, 1, 'US 9', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(229, 1, 'US 9.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(230, 1, 'US 10', 'US', 'Men', 4, 1, '2026-01-03 15:18:53'),
(231, 1, 'US 10.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(232, 1, 'US 11', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(233, 1, 'US 11.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(234, 1, 'US 12', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(235, 1, 'US 12.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(236, 1, 'US 13', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(237, 2, 'US 6.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(238, 2, 'US 7.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(239, 2, 'US 8.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(240, 2, 'US 9.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(241, 2, 'US 10', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(242, 2, 'US 10.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(243, 2, 'US 11', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(244, 2, 'US 11.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(245, 2, 'US 12', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(246, 2, 'US 12.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(247, 2, 'US 13', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(248, 3, 'US 6', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(249, 3, 'US 6.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(250, 3, 'US 7', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(251, 3, 'US 7.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(252, 3, 'US 8', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(253, 3, 'US 8.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(254, 3, 'US 9', 'US', 'Men', 3, 1, '2026-01-03 15:18:53'),
(255, 3, 'US 9.5', 'US', 'Men', 3, 1, '2026-01-03 15:18:53'),
(256, 3, 'US 10.5', 'US', 'Men', 3, 1, '2026-01-03 15:18:53'),
(257, 3, 'US 11.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(258, 3, 'US 12', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(259, 3, 'US 12.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(260, 3, 'US 13', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(261, 4, 'US 6', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(262, 4, 'US 6.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(263, 4, 'US 7.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(264, 4, 'US 8.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(265, 4, 'US 9.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(266, 4, 'US 10.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(267, 4, 'US 11', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(268, 4, 'US 11.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(269, 4, 'US 12', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(270, 4, 'US 12.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(271, 4, 'US 13', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(272, 5, 'US 6', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(273, 5, 'US 6.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(274, 5, 'US 7', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(275, 5, 'US 7.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(276, 5, 'US 8', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(277, 5, 'US 8.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(278, 5, 'US 9.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(279, 5, 'US 10.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(280, 5, 'US 11.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(281, 5, 'US 12.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(282, 5, 'US 13', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(283, 6, 'US 6.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(284, 6, 'US 7.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(285, 6, 'US 8.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(286, 6, 'US 9.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(287, 6, 'US 10', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(288, 6, 'US 10.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(289, 6, 'US 11', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(290, 6, 'US 11.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(291, 6, 'US 12', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(292, 6, 'US 12.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(293, 6, 'US 13', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(294, 7, 'US 6', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(295, 7, 'US 6.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(296, 7, 'US 7', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(297, 7, 'US 7.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(298, 7, 'US 8', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(299, 7, 'US 8.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(300, 7, 'US 9.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(301, 7, 'US 10.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(302, 7, 'US 11', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(303, 7, 'US 11.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(304, 7, 'US 12', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(305, 7, 'US 12.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(306, 7, 'US 13', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(307, 8, 'US 6.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(308, 8, 'US 7.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(309, 8, 'US 8.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(310, 8, 'US 9.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(311, 8, 'US 10', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(312, 8, 'US 10.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(313, 8, 'US 11', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(314, 8, 'US 11.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(315, 8, 'US 12', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(316, 8, 'US 12.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(317, 8, 'US 13', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(318, 9, 'US 6.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(319, 9, 'US 7.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(320, 9, 'US 8.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(321, 9, 'US 9.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(322, 9, 'US 10', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(323, 9, 'US 10.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(324, 9, 'US 11', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(325, 9, 'US 11.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(326, 9, 'US 12', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(327, 9, 'US 12.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(328, 9, 'US 13', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(329, 10, 'US 6', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(330, 10, 'US 6.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(331, 10, 'US 7', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(332, 10, 'US 7.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(333, 10, 'US 8', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(334, 10, 'US 8.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(335, 10, 'US 9.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(336, 10, 'US 10.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(337, 10, 'US 11', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(338, 10, 'US 11.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(339, 10, 'US 12', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(340, 10, 'US 12.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(341, 10, 'US 13', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(342, 11, 'US 6', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(343, 11, 'US 6.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(344, 11, 'US 7.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(345, 11, 'US 8.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(346, 11, 'US 9.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(347, 11, 'US 10.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(348, 11, 'US 11', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(349, 11, 'US 11.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(350, 11, 'US 12', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(351, 11, 'US 12.5', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(352, 11, 'US 13', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
(479, 3, 'US W 7', 'US', 'Women', 0, 1, '2026-01-03 16:47:49'),
(480, 3, 'US W 10', 'US', 'Men', 0, 1, '2026-01-03 16:47:57'),
(481, 3, 'US W 10', 'US', 'Women', 2, 1, '2026-01-03 16:48:12');

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
  ADD UNIQUE KEY `uq_products_sku` (`sku`),
  ADD KEY `idx_products_sport` (`sport`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_sizes_label` (`product_id`,`size_label`,`gender`,`size_system`),
  ADD KEY `idx_product_sizes_product` (`product_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=482;

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
