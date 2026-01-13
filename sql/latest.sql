-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2026 at 03:20 PM
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
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body_html` longtext NOT NULL,
  `body_text` longtext DEFAULT NULL,
  `embedded_json` longtext DEFAULT NULL,
  `status` enum('queued','sending','sent','failed') NOT NULL DEFAULT 'queued',
  `attempts` int(11) NOT NULL DEFAULT 0,
  `last_error` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_queue`
--

INSERT INTO `email_queue` (`id`, `recipient`, `subject`, `body_html`, `body_text`, `embedded_json`, `status`, `attempts`, `last_error`, `created_at`, `sent_at`) VALUES
(1, 'jamescarlorivera52@gmail.com', 'Your SoleSource Receipt #SO-20260106153349-2645', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260106153349-2645</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260106153349-2645</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\">January 6, 2026</strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">PayPal</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://dev.art2cart.shop/assets/img/products/air-force-1.png\" alt=\"AIR FORCE 1\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">AIR FORCE 1</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: US 10 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱4,999.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱4,999.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱4,999.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=12\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James\nOrder: SO-20260106153349-2645\nTotal: ₱4,999.00\nPayment: PayPal\nShip to: blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=12', NULL, 'sent', 1, NULL, '2026-01-06 22:33:49', '2026-01-06 22:34:26'),
(2, 'jamescarlorivera52@gmail.com', 'Your SoleSource Receipt #SO-20260106154127-1576', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260106154127-1576</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260106154127-1576</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\">January 6, 2026</strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">PayPal</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://dev.art2cart.shop/assets/img/products/air-force-1.png\" alt=\"AIR FORCE 1\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">AIR FORCE 1</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: US 10 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱4,999.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱4,999.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱4,999.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=13\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James\nOrder: SO-20260106154127-1576\nTotal: ₱4,999.00\nPayment: PayPal\nShip to: blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=13', NULL, 'sent', 1, NULL, '2026-01-06 22:41:27', '2026-01-06 22:49:31'),
(3, 'jamescarlorivera52@gmail.com', 'Your SoleSource Receipt #SO-20260106155302-4530', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260106155302-4530</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260106155302-4530</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\"></strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">COD</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://dev.art2cart.shop/assets/img/products/jordan-11-legend-blue.png\" alt=\"Jordan 11 Retro Legend Blue\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">Jordan 11 Retro Legend Blue</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: US 9 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱12,000.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱12,000.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱12,000.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=14\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James\nOrder: SO-20260106155302-4530\nTotal: ₱12,000.00\nPayment: COD\nShip to: blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=14', NULL, 'sent', 1, NULL, '2026-01-06 22:53:02', '2026-01-06 22:53:24'),
(4, 'jamescarlorivera52@gmail.com', 'Your SoleSource Receipt #SO-20260106163659-9962', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260106163659-9962</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260106163659-9962</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\">January 6, 2026</strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">PayPal</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://dev.art2cart.shop/assets/img/products/asics-gel-kayano-14.png\" alt=\"GEL-KAYANO 14\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">GEL-KAYANO 14</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: US 9 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱9,490.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱9,490.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱9,490.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=15\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James\nOrder: SO-20260106163659-9962\nTotal: ₱9,490.00\nPayment: PayPal\nShip to: blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=15', NULL, 'sent', 1, NULL, '2026-01-06 23:36:59', '2026-01-06 23:37:05'),
(5, 'james@solesource.com', 'Your SoleSource Receipt #SO-20260108035644-7849', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260108035644-7849</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260108035644-7849</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\">January 8, 2026</strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James Carlo.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">PayPal</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James Carlo</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://dev.art2cart.shop/assets/img/products/jordan-11-legend-blue.png\" alt=\"Jordan 11 Retro Legend Blue\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">Jordan 11 Retro Legend Blue</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: US 9 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱12,000.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱12,000.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱12,000.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=16\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James Carlo\nOrder: SO-20260108035644-7849\nTotal: ₱12,000.00\nPayment: PayPal\nShip to: BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=16', NULL, 'sent', 1, NULL, '2026-01-08 10:56:44', '2026-01-08 10:57:06'),
(6, 'james@solesource.com', 'Your SoleSource Receipt #SO-20260108044457-3498', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260108044457-3498</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260108044457-3498</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\">January 8, 2026</strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James Carlo.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">PayPal</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James Carlo</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://dev.art2cart.shop/assets/img/products/jordan-11-legend-blue.png\" alt=\"Jordan 11 Retro Legend Blue\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">Jordan 11 Retro Legend Blue</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: US 11 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱12,000.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱12,000.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱12,000.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=17\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James Carlo\nOrder: SO-20260108044457-3498\nTotal: ₱12,000.00\nPayment: PayPal\nShip to: BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=17', NULL, 'sent', 1, NULL, '2026-01-08 11:44:57', '2026-01-08 11:45:06'),
(7, 'james@solesource.com', 'Your SoleSource Receipt #SO-20260108044731-7428', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260108044731-7428</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260108044731-7428</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\"></strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James Carlo.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">COD</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James Carlo</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://dev.art2cart.shop/assets/img/products/jordan-11-legend-blue.png\" alt=\"Jordan 11 Retro Legend Blue\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">Jordan 11 Retro Legend Blue</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: US 11 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱12,000.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱12,000.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱12,000.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=18\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James Carlo\nOrder: SO-20260108044731-7428\nTotal: ₱12,000.00\nPayment: COD\nShip to: BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=18', NULL, 'sent', 1, NULL, '2026-01-08 11:47:31', '2026-01-08 11:47:44');
INSERT INTO `email_queue` (`id`, `recipient`, `subject`, `body_html`, `body_text`, `embedded_json`, `status`, `attempts`, `last_error`, `created_at`, `sent_at`) VALUES
(8, 'jamescarlorivera52@gmail.com', 'Your SoleSource Receipt #SO-20260108044904-3231', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260108044904-3231</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260108044904-3231</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\"></strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">COD</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://dev.art2cart.shop/assets/img/products/jordan-11-legend-blue.png\" alt=\"Jordan 11 Retro Legend Blue\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">Jordan 11 Retro Legend Blue</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: US 11 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱12,000.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱12,000.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱12,000.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=19\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James\nOrder: SO-20260108044904-3231\nTotal: ₱12,000.00\nPayment: COD\nShip to: blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=19', NULL, 'sent', 1, NULL, '2026-01-08 11:49:04', '2026-01-08 11:49:44'),
(9, 'jamescarlorivera52@gmail.com', 'Your SoleSource Receipt #SO-20260108045212-7574', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260108045212-7574</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260108045212-7574</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\">January 8, 2026</strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">PayPal</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://dev.art2cart.shop/assets/img/products/air-force-1.png\" alt=\"AIR FORCE 1\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">AIR FORCE 1</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: US 10 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱4,999.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱4,999.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱4,999.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=20\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James\nOrder: SO-20260108045212-7574\nTotal: ₱4,999.00\nPayment: PayPal\nShip to: blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=20', NULL, 'sent', 1, NULL, '2026-01-08 11:52:12', '2026-01-08 11:57:05'),
(10, 'jamescarlorivera52@gmail.com', 'Your SoleSource Receipt #SO-20260108045532-2589', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260108045532-2589</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260108045532-2589</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\">January 8, 2026</strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">PayPal</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://dev.art2cart.shop/assets/img/products/air-force-1.png\" alt=\"AIR FORCE 1\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">AIR FORCE 1</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: US M 9 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱4,999.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱4,999.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱4,999.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=21\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James\nOrder: SO-20260108045532-2589\nTotal: ₱4,999.00\nPayment: PayPal\nShip to: blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=21', NULL, 'sent', 1, NULL, '2026-01-08 11:55:32', '2026-01-08 11:57:10'),
(11, 'jamescarlorivera52@gmail.com', 'Your SoleSource Receipt #SO-20260108112207-7708', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260108112207-7708</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260108112207-7708</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\">January 8, 2026</strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">PayPal</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://via.placeholder.com/120x120.png?text=SoleSource\" alt=\"test\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">test</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: 7 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱11.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱11.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱11.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=22\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James\nOrder: SO-20260108112207-7708\nTotal: ₱11.00\nPayment: PayPal\nShip to: blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=22', NULL, 'sent', 1, NULL, '2026-01-08 18:22:07', '2026-01-08 18:23:06'),
(12, 'jamescarlorivera52@gmail.com', 'Your SoleSource Receipt #SO-20260108125640-4590', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260108125640-4590</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260108125640-4590</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\"></strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">COD</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://dev.art2cart.shop/assets/img/products/adidas-gazelle-indoor.jpg\" alt=\"GAZELLE INDOOR\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">GAZELLE INDOOR</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: US 6 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱4,700.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱4,700.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱4,700.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=23\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James\nOrder: SO-20260108125640-4590\nTotal: ₱4,700.00\nPayment: COD\nShip to: blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=23', NULL, 'sent', 1, NULL, '2026-01-08 19:56:40', '2026-01-08 19:57:06'),
(13, 'jamescarlorivera52@gmail.com', 'Your SoleSource Receipt #SO-20260108135938-1593', '<!DOCTYPE html><html><body style=\"margin:0; padding:0; background:#F8F9FA;\">\r\n    <div style=\"max-width:760px; margin:24px auto; background:#fff; font-family:Arial,sans-serif; color:#121212; border:1px solid #e6e6e6; box-shadow:0 2px 10px rgba(0,0,0,0.04);\">\r\n        <div style=\"height:6px; background:#E9713F;\"></div>\r\n        <div style=\"padding:14px 20px; border-bottom:1px solid #efefef; display:flex; align-items:center; justify-content:space-between;\">\r\n            <img src=\"https://dev.art2cart.shop/assets/img/logo-big.png\" alt=\"SoleSource\" height=\"28\" style=\"display:block;\">\r\n            <div style=\"font-size:12px; color:#6f6f6f; text-align:right;\">Order #SO-20260108135938-1593</div>\r\n        </div>\r\n\r\n        <div style=\"padding:24px 24px 8px 24px; text-align:center;\">\r\n            <div style=\"font-size:22px; font-weight:800; letter-spacing:0.4px; color:#121212;\">Thank you!</div>\r\n            <div style=\"margin-top:8px; color:#6f6f6f; font-size:13px;\">Your order was placed successfully. Track or view anytime.</div>\r\n        </div>\r\n\r\n        <div style=\"margin:0 24px; background:#333333; color:#fff; padding:18px 16px; font-weight:700; letter-spacing:0.3px; font-size:15px; border-radius:8px;\">\r\n            YOUR ORDER WAS PLACED SUCCESSFULLY.\r\n            <div style=\"font-weight:400; margin-top:6px; color:#eaeaea; font-size:12px;\">We also emailed your confirmation.</div>\r\n        </div>\r\n\r\n        <div style=\"padding:18px 24px 8px 24px; font-size:13px; color:#6f6f6f; line-height:1.6;\">\r\n            <div style=\"margin-bottom:4px;\">Your Order: <strong style=\"color:#121212;\">SO-20260108135938-1593</strong></div>\r\n            <div style=\"margin-bottom:10px;\">Order Date: <strong style=\"color:#121212;\">January 8, 2026</strong></div>\r\n            <div style=\"margin-bottom:12px;\">We have sent the order confirmation details to James.</div>\r\n        </div>\r\n\r\n        <div style=\"display:flex; gap:12px; padding:0 24px 18px 24px;\">\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">SHIPMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f; line-height:1.6;\">blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines</div>\r\n            </div>\r\n            <div style=\"flex:1; border:1px solid #efefef; border-radius:8px; padding:12px;\">\r\n                <div style=\"font-weight:800; font-size:12px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">PAYMENT</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Method</div>\r\n                <div style=\"font-weight:700; color:#121212; margin-bottom:6px;\">PayPal</div>\r\n                <div style=\"font-size:13px; color:#6f6f6f;\">Billing</div>\r\n                <div style=\"font-weight:700; color:#121212;\">James</div>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:0 24px 10px 24px;\">\r\n            <div style=\"font-weight:800; font-size:13px; letter-spacing:0.4px; margin-bottom:6px; color:#121212;\">DELIVERY</div>\r\n            <table role=\"presentation\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse; font-size:14px;\">\r\n                <tbody><tr>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; vertical-align:top; width:90px;\"><img src=\"https://dev.art2cart.shop/assets/img/products/air-force-1.png\" alt=\"AIR FORCE 1\" width=\"80\" style=\"display:block; border-radius:6px;\" referrerpolicy=\"no-referrer\"></td>\r\n            <td style=\"padding:14px 0 14px 10px; border-top:1px solid #ececec; font-size:13px; color:#222;\"><div style=\"font-weight:700; font-size:14px; color:#121212;\">AIR FORCE 1</div><div style=\"margin-top:4px; color:#6f6f6f;\">Size: US M 9 | Qty: 1</div></td>\r\n            <td style=\"padding:14px 0; border-top:1px solid #ececec; text-align:right; font-weight:700; color:#121212; white-space:nowrap;\">₱4,999.00</td>\r\n        </tr></tbody>\r\n            </table>\r\n        </div>\r\n\r\n        <div style=\"padding:14px 24px 8px 24px;\">\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212;\">\r\n                <span>Subtotal</span><span>₱4,999.00</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Shipping</span><span>-</span>\r\n            </div>\r\n            <div style=\"font-size:14px; display:flex; justify-content:space-between; color:#121212; margin-top:4px;\">\r\n                <span>Estimated Tax</span><span>-</span>\r\n            </div>\r\n            <div style=\"margin-top:10px; font-weight:800; font-size:16px; display:flex; justify-content:space-between; color:#121212; border-top:1px solid #efefef; padding-top:10px;\">\r\n                <span>Total</span><span>₱4,999.00</span>\r\n            </div>\r\n        </div>\r\n\r\n        <div style=\"padding:16px 24px 24px 24px; text-align:right;\">\r\n            <a href=\"https://dev.art2cart.shop/view_order.php?id=24\" style=\"display:inline-block; background:#E9713F; color:#fff; padding:12px 18px; text-decoration:none; font-weight:800; letter-spacing:0.4px; border-radius:4px;\">View / Print</a>\r\n        </div>\r\n\r\n        <div style=\"background:#121212; color:#bfbfbf; font-size:11px; text-align:center; padding:12px 10px;\">\r\n            If you have questions, reply to this email.\r\n        </div>\r\n    </div>\r\n</body></html>', 'Thanks for your order James\nOrder: SO-20260108135938-1593\nTotal: ₱4,999.00\nPayment: PayPal\nShip to: blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines\nView: https://dev.art2cart.shop/view_order.php?id=24', NULL, 'sent', 1, NULL, '2026-01-08 20:59:38', '2026-01-08 21:00:05');

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
(1, 2, 'SO-20260103162005-8100', 9490.00, 'PayPal', 'delivered', '09457996892', NULL, 'James Carlo', 'BLK 27 LOT 25 WINE CUP ST', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-4840298B', 'MockExpress', '2026-01-03 15:20:05'),
(2, 4, 'SO-20260105093653-8725', 12000.00, 'COD', 'delivered', '09515092559', NULL, 'Zakeesha Elisha Canubas', 'Purok 4, Silangan, San Luis, Sto Tomas City', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Luis', '4234', 'Philippines', 'Purok 4, Silangan, San Luis, Sto Tomas City, San Luis, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-3CC9B5E1', 'MockExpress', '2026-01-05 08:36:53'),
(3, 2, 'SO-20260106132407-9811', 12000.00, 'PayPal', 'delivered', '09457996892', NULL, 'James Carlo', 'BLK 27 LOT 25 WINE CUP ST', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-3CCCF9F7', 'MockExpress', '2026-01-06 12:24:07'),
(4, 2, 'SO-20260106132805-5597', 4700.00, 'PayPal', 'delivered', '09457996892', NULL, 'James Carlo', 'BLK 27 LOT 25 WINE CUP ST', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-105696', 'MockExpress', '2026-01-06 12:28:05'),
(5, 7, 'SO-20260106133047-4480', 4700.00, 'PayPal', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-861444', 'MockExpress', '2026-01-06 12:30:47'),
(6, 7, 'SO-20260106143006-1910', 4700.00, 'COD', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-990138', 'MockExpress', '2026-01-06 13:30:06'),
(7, 7, 'SO-20260106143309-5139', 11.00, 'PayPal', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-366358', 'MockExpress', '2026-01-06 13:33:09'),
(8, 7, 'SO-20260106143430-9299', 4999.00, 'PayPal', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-861377', 'MockExpress', '2026-01-06 13:34:30'),
(9, 7, 'SO-20260106143815-3139', 9998.00, 'PayPal', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-207812', 'MockExpress', '2026-01-06 13:38:15'),
(10, 7, 'SO-20260106144332-2884', 9490.00, 'PayPal', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-454929', 'MockExpress', '2026-01-06 13:43:32'),
(11, 7, 'SO-20260106144737-2943', 12000.00, 'PayPal', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-651209', 'MockExpress', '2026-01-06 13:47:37'),
(12, 7, 'SO-20260106153349-2645', 4999.00, 'PayPal', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-891260', 'MockExpress', '2026-01-06 14:33:49'),
(13, 7, 'SO-20260106154127-1576', 4999.00, 'PayPal', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-502677', 'MockExpress', '2026-01-06 14:41:27'),
(14, 7, 'SO-20260106155302-4530', 12000.00, 'COD', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-839606', 'MockExpress', '2026-01-06 14:53:02'),
(15, 7, 'SO-20260106163659-9962', 9490.00, 'PayPal', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-689998', 'MockExpress', '2026-01-06 15:36:59'),
(16, 2, 'SO-20260108035644-7849', 12000.00, 'PayPal', 'delivered', '09457996892', NULL, 'James Carlo', 'BLK 27 LOT 25 WINE CUP ST', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-931175', 'MockExpress', '2026-01-08 02:56:44'),
(17, 2, 'SO-20260108044457-3498', 12000.00, 'PayPal', 'delivered', '09457996892', NULL, 'James Carlo', 'BLK 27 LOT 25 WINE CUP ST', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-585884', 'MockExpress', '2026-01-08 03:44:57'),
(18, 2, 'SO-20260108044731-7428', 12000.00, 'COD', 'delivered', '09457996892', NULL, 'James Carlo', 'BLK 27 LOT 25 WINE CUP ST', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'BLK 27 LOT 25 WINE CUP ST, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-135894', 'MockExpress', '2026-01-08 03:47:31'),
(19, 7, 'SO-20260108044904-3231', 12000.00, 'COD', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-921816', 'MockExpress', '2026-01-08 03:49:04'),
(20, 7, 'SO-20260108045212-7574', 4999.00, 'PayPal', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-201403', 'MockExpress', '2026-01-08 03:52:12'),
(21, 7, 'SO-20260108045532-2589', 4999.00, 'PayPal', 'delivered', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-241566', 'MockExpress', '2026-01-08 03:55:32'),
(22, 7, 'SO-20260108112207-7708', 11.00, 'PayPal', 'shipped', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', 'MOCK-603622', 'MockExpress', '2026-01-08 10:22:07'),
(23, 7, 'SO-20260108125640-4590', 4700.00, 'COD', 'pending', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', NULL, NULL, '2026-01-08 11:56:40'),
(24, 7, 'SO-20260108135938-1593', 4999.00, 'PayPal', 'confirmed', '09457996892', NULL, 'James', 'blk 27 lot 25 Wine cup street', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'San Rafael', '4234', 'Philippines', 'blk 27 lot 25 Wine cup street, San Rafael, Santo Tomas, Batangas, Region IV-A (CALABARZON), 4234, Philippines', NULL, NULL, '2026-01-08 12:59:38');

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
(1, 1, 3, 15, 'US 10', 1, 9490.00, '2026-01-03 15:20:05'),
(2, 2, 5, 23, 'US 10', 1, 12000.00, '2026-01-05 08:36:53'),
(3, 3, 5, 23, 'US 10', 1, 12000.00, '2026-01-06 12:24:07'),
(4, 4, 2, 7, 'US 6', 1, 4700.00, '2026-01-06 12:28:05'),
(5, 5, 2, 10, 'US 9', 1, 4700.00, '2026-01-06 12:30:47'),
(6, 6, 2, 7, 'US 6', 1, 4700.00, '2026-01-06 13:30:06'),
(7, 7, 19, 489, '7', 1, 11.00, '2026-01-06 13:33:09'),
(8, 8, 1, 230, 'US 10', 1, 4999.00, '2026-01-06 13:34:30'),
(9, 9, 1, 2, 'US M 8', 2, 4999.00, '2026-01-06 13:38:15'),
(10, 10, 3, 15, 'US 10', 1, 9490.00, '2026-01-06 13:43:32'),
(11, 11, 5, 23, 'US 10', 1, 12000.00, '2026-01-06 13:47:37'),
(12, 12, 1, 230, 'US 10', 1, 4999.00, '2026-01-06 14:33:49'),
(13, 13, 1, 230, 'US 10', 1, 4999.00, '2026-01-06 14:41:27'),
(14, 14, 5, 22, 'US 9', 1, 12000.00, '2026-01-06 14:53:02'),
(15, 15, 3, 254, 'US 9', 1, 9490.00, '2026-01-06 15:36:59'),
(16, 16, 5, 22, 'US 9', 1, 12000.00, '2026-01-08 02:56:44'),
(17, 17, 5, 24, 'US 11', 1, 12000.00, '2026-01-08 03:44:57'),
(18, 18, 5, 24, 'US 11', 1, 12000.00, '2026-01-08 03:47:31'),
(19, 19, 5, 24, 'US 11', 1, 12000.00, '2026-01-08 03:49:04'),
(20, 20, 1, 230, 'US 10', 1, 4999.00, '2026-01-08 03:52:12'),
(21, 21, 1, 3, 'US M 9', 1, 4999.00, '2026-01-08 03:55:32'),
(22, 22, 19, 489, '7', 1, 11.00, '2026-01-08 10:22:07'),
(23, 23, 2, 7, 'US 6', 1, 4700.00, '2026-01-08 11:56:40'),
(24, 24, 1, 3, 'US M 9', 1, 4999.00, '2026-01-08 12:59:38');

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
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `provider_order_id` varchar(100) DEFAULT NULL,
  `provider_capture_id` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `raw_payload` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_transactions`
--

INSERT INTO `payment_transactions` (`id`, `order_id`, `provider`, `provider_order_id`, `provider_capture_id`, `status`, `raw_payload`, `created_at`) VALUES
(1, 3, 'PayPal', '4Y70461385980714R', '06E61286PH435222Y', 'captured', '{\"id\":\"WH-6ST39941JW192021R-6Y947172HC014333V\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-06T12:23:51.787Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 12000.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"12000.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"4Y70461385980714R\"}},\"update_time\":\"2026-01-06T12:23:47Z\",\"create_time\":\"2026-01-06T12:23:47Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"423.00\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"12000.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"11577.00\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/06E61286PH435222Y\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/06E61286PH435222Y\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/4Y70461385980714R\"}],\"id\":\"06E61286PH435222Y\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-6ST39941JW192021R-6Y947172HC014333V\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-6ST39941JW192021R-6Y947172HC014333V\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-06 12:24:07'),
(2, 4, 'PayPal', '5EM51994RR869040N', '3J947736H9528204L', 'captured', '{\"id\":\"WH-0240643023099710P-8LK62757YG6340746\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-06T12:27:49.064Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 4700.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"4700.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"5EM51994RR869040N\"}},\"update_time\":\"2026-01-06T12:27:45Z\",\"create_time\":\"2026-01-06T12:27:45Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"174.80\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"4700.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"4525.20\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/3J947736H9528204L\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/3J947736H9528204L\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/5EM51994RR869040N\"}],\"id\":\"3J947736H9528204L\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-0240643023099710P-8LK62757YG6340746\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-0240643023099710P-8LK62757YG6340746\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-06 12:28:05'),
(3, 5, 'PayPal', '1EY718661X8739548', '6V134128NY9782604', 'captured', '{\"id\":\"WH-9BR73334AC1213457-9T110774J8881310K\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-06T12:30:31.169Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 4700.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"4700.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"1EY718661X8739548\"}},\"update_time\":\"2026-01-06T12:30:27Z\",\"create_time\":\"2026-01-06T12:30:27Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"174.80\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"4700.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"4525.20\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/6V134128NY9782604\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/6V134128NY9782604\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/1EY718661X8739548\"}],\"id\":\"6V134128NY9782604\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-9BR73334AC1213457-9T110774J8881310K\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-9BR73334AC1213457-9T110774J8881310K\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-06 12:30:47'),
(4, 7, 'PayPal', '71T601134X6534410', '8HG72033Y0215441J', 'captured', '{\"id\":\"WH-2DM98624SY267114U-67A90348FC8787845\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-06T13:32:52.131Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 11.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"11.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"71T601134X6534410\"}},\"update_time\":\"2026-01-06T13:32:48Z\",\"create_time\":\"2026-01-06T13:32:48Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"11.00\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"11.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"0.00\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/8HG72033Y0215441J\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/8HG72033Y0215441J\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/71T601134X6534410\"}],\"id\":\"8HG72033Y0215441J\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-2DM98624SY267114U-67A90348FC8787845\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-2DM98624SY267114U-67A90348FC8787845\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-06 13:33:09'),
(5, 8, 'PayPal', '2FD762035U006691J', '2LL297576R602844L', 'captured', '{\"id\":\"WH-6CE22255RG021371M-15533706L2268293P\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-06T13:34:13.928Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 4999.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"4999.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"2FD762035U006691J\"}},\"update_time\":\"2026-01-06T13:34:09Z\",\"create_time\":\"2026-01-06T13:34:09Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"184.97\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"4999.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"4814.03\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/2LL297576R602844L\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/2LL297576R602844L\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/2FD762035U006691J\"}],\"id\":\"2LL297576R602844L\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-6CE22255RG021371M-15533706L2268293P\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-6CE22255RG021371M-15533706L2268293P\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-06 13:34:30'),
(6, 9, 'PayPal', '8WF13938K2019034G', '52T0417939942004J', 'captured', '{\"id\":\"WH-9GJ28043W95021518-86B10454G44010746\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-06T13:37:59.920Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 9998.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"9998.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"8WF13938K2019034G\"}},\"update_time\":\"2026-01-06T13:37:54Z\",\"create_time\":\"2026-01-06T13:37:54Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"354.93\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"9998.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"9643.07\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/52T0417939942004J\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/52T0417939942004J\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/8WF13938K2019034G\"}],\"id\":\"52T0417939942004J\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-9GJ28043W95021518-86B10454G44010746\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-9GJ28043W95021518-86B10454G44010746\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-06 13:38:15'),
(7, 10, 'PayPal', '8SD002980Y848721C', '96A29656KY061750P', 'approved', '{\"id\":\"WH-1WC62158YY416751S-5NN31356X1045290B\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-06T13:43:09.330Z\",\"resource_type\":\"checkout-order\",\"resource_version\":\"2.0\",\"event_type\":\"CHECKOUT.ORDER.APPROVED\",\"summary\":\"An order has been approved by buyer\",\"resource\":{\"create_time\":\"2026-01-06T13:42:59Z\",\"purchase_units\":[{\"reference_id\":\"default\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"9490.00\"},\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"supplementary_data\":{\"tax_nexus\":[]}}],\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/8SD002980Y848721C\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/8SD002980Y848721C\",\"rel\":\"update\",\"method\":\"PATCH\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/8SD002980Y848721C\\/capture\",\"rel\":\"capture\",\"method\":\"POST\"}],\"id\":\"8SD002980Y848721C\",\"payment_source\":{\"paypal\":{\"email_address\":\"sb-ooty948571342@personal.example.com\",\"account_id\":\"PPK5U4F2HK5LS\",\"account_status\":\"VERIFIED\",\"name\":{\"given_name\":\"John\",\"surname\":\"Doe\"},\"address\":{\"country_code\":\"PH\"}}},\"intent\":\"CAPTURE\",\"payer\":{\"name\":{\"given_name\":\"John\",\"surname\":\"Doe\"},\"email_address\":\"sb-ooty948571342@personal.example.com\",\"payer_id\":\"PPK5U4F2HK5LS\",\"address\":{\"country_code\":\"PH\"}},\"status\":\"APPROVED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-1WC62158YY416751S-5NN31356X1045290B\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-1WC62158YY416751S-5NN31356X1045290B\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-06 13:43:32'),
(8, 11, 'PayPal', '0TE51757465344335', '87C17887JB505604L', 'captured', '{\"id\":\"WH-46F32692BG7914841-87981447DP835821J\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-06T13:47:19.869Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 12000.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"12000.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"0TE51757465344335\"}},\"update_time\":\"2026-01-06T13:47:16Z\",\"create_time\":\"2026-01-06T13:47:16Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"423.00\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"12000.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"11577.00\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/87C17887JB505604L\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/87C17887JB505604L\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/0TE51757465344335\"}],\"id\":\"87C17887JB505604L\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-46F32692BG7914841-87981447DP835821J\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-46F32692BG7914841-87981447DP835821J\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-06 13:47:37'),
(9, 12, 'PayPal', '2GK70731MT611323H', '3RU42018X00740643', 'captured', '{\"id\":\"WH-78175711MP402914U-7PL245960G2828144\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-06T14:33:33.845Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 4999.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"4999.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"2GK70731MT611323H\"}},\"update_time\":\"2026-01-06T14:33:29Z\",\"create_time\":\"2026-01-06T14:33:29Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"184.97\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"4999.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"4814.03\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/3RU42018X00740643\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/3RU42018X00740643\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/2GK70731MT611323H\"}],\"id\":\"3RU42018X00740643\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-78175711MP402914U-7PL245960G2828144\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-78175711MP402914U-7PL245960G2828144\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-06 14:33:49'),
(10, 13, 'PayPal', '0KY889820M893484S', '6DC13677D7324025V', 'captured', '{\"id\":\"WH-14B79693XV002205P-2N3639549L802102W\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-06T14:41:10.884Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 4999.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"4999.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"0KY889820M893484S\"}},\"update_time\":\"2026-01-06T14:41:07Z\",\"create_time\":\"2026-01-06T14:41:07Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"184.97\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"4999.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"4814.03\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/6DC13677D7324025V\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/6DC13677D7324025V\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/0KY889820M893484S\"}],\"id\":\"6DC13677D7324025V\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-14B79693XV002205P-2N3639549L802102W\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-14B79693XV002205P-2N3639549L802102W\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-06 14:41:27'),
(11, 15, 'PayPal', '4CY372348H126204M', '9AK13741D6406972A', 'captured', '{\"id\":\"WH-22E76875TL079974J-60R55746K56452742\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-06T15:36:42.541Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 9490.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"9490.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"4CY372348H126204M\"}},\"update_time\":\"2026-01-06T15:36:38Z\",\"create_time\":\"2026-01-06T15:36:38Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"337.66\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"9490.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"9152.34\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/9AK13741D6406972A\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/9AK13741D6406972A\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/4CY372348H126204M\"}],\"id\":\"9AK13741D6406972A\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-22E76875TL079974J-60R55746K56452742\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-22E76875TL079974J-60R55746K56452742\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-06 15:36:59'),
(12, 16, 'PayPal', '60B87872FS8562638', '2XF41070Y0805872M', 'captured', '{\"id\":\"WH-24P87404D3840935E-4XW61557SB850040M\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-08T02:56:25.039Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 12000.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"12000.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"60B87872FS8562638\"}},\"update_time\":\"2026-01-08T02:56:21Z\",\"create_time\":\"2026-01-08T02:56:21Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"423.00\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"12000.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"11577.00\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/2XF41070Y0805872M\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/2XF41070Y0805872M\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/60B87872FS8562638\"}],\"id\":\"2XF41070Y0805872M\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-24P87404D3840935E-4XW61557SB850040M\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-24P87404D3840935E-4XW61557SB850040M\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-08 02:56:44'),
(13, 17, 'PayPal', '5FP10782VJ540374E', '7W1594101F388883C', 'captured', '{\"id\":\"WH-8FG95952TR6531628-0EL49677XB977835W\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-08T03:44:38.551Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 12000.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"12000.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"5FP10782VJ540374E\"}},\"update_time\":\"2026-01-08T03:44:34Z\",\"create_time\":\"2026-01-08T03:44:34Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"423.00\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"12000.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"11577.00\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/7W1594101F388883C\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/7W1594101F388883C\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/5FP10782VJ540374E\"}],\"id\":\"7W1594101F388883C\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-8FG95952TR6531628-0EL49677XB977835W\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-8FG95952TR6531628-0EL49677XB977835W\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-08 03:44:57'),
(14, 20, 'PayPal', '6XY91446M0050735U', '9B385409BE7776503', 'captured', '{\"id\":\"WH-69458322GF570330X-65068226UL837360R\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-08T03:51:53.308Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 4999.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"4999.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"6XY91446M0050735U\"}},\"update_time\":\"2026-01-08T03:51:49Z\",\"create_time\":\"2026-01-08T03:51:49Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"184.97\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"4999.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"4814.03\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/9B385409BE7776503\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/9B385409BE7776503\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/6XY91446M0050735U\"}],\"id\":\"9B385409BE7776503\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-69458322GF570330X-65068226UL837360R\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-69458322GF570330X-65068226UL837360R\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-08 03:52:12'),
(15, 21, 'PayPal', '80T97489X6302912M', '4B0360888J3121717', 'captured', '{\"id\":\"WH-1LE1606334620561G-8SM86373UH631373K\",\"event_version\":\"1.0\",\"create_time\":\"2026-01-08T03:55:13.478Z\",\"resource_type\":\"capture\",\"resource_version\":\"2.0\",\"event_type\":\"PAYMENT.CAPTURE.COMPLETED\",\"summary\":\"Payment completed for \\u20b1 4999.0 PHP\",\"resource\":{\"payee\":{\"email_address\":\"sb-khkjx48578478@business.example.com\",\"merchant_id\":\"H3HNBEFNJVB5W\"},\"amount\":{\"value\":\"4999.00\",\"currency_code\":\"PHP\"},\"seller_protection\":{\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"],\"status\":\"ELIGIBLE\"},\"supplementary_data\":{\"related_ids\":{\"order_id\":\"80T97489X6302912M\"}},\"update_time\":\"2026-01-08T03:55:09Z\",\"create_time\":\"2026-01-08T03:55:09Z\",\"final_capture\":true,\"seller_receivable_breakdown\":{\"paypal_fee\":{\"value\":\"184.97\",\"currency_code\":\"PHP\"},\"gross_amount\":{\"value\":\"4999.00\",\"currency_code\":\"PHP\"},\"net_amount\":{\"value\":\"4814.03\",\"currency_code\":\"PHP\"}},\"links\":[{\"method\":\"GET\",\"rel\":\"self\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/4B0360888J3121717\"},{\"method\":\"POST\",\"rel\":\"refund\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/4B0360888J3121717\\/refund\"},{\"method\":\"GET\",\"rel\":\"up\",\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/80T97489X6302912M\"}],\"id\":\"4B0360888J3121717\",\"status\":\"COMPLETED\"},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-1LE1606334620561G-8SM86373UH631373K\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v1\\/notifications\\/webhooks-events\\/WH-1LE1606334620561G-8SM86373UH631373K\\/resend\",\"rel\":\"resend\",\"method\":\"POST\"}]}', '2026-01-08 03:55:32'),
(16, 22, 'PayPal', '6H799260H9019635L', '1VK16968M2684913B', 'captured', '{\"id\":\"6H799260H9019635L\",\"status\":\"COMPLETED\",\"payment_source\":{\"paypal\":{\"email_address\":\"sb-ooty948571342@personal.example.com\",\"account_id\":\"PPK5U4F2HK5LS\",\"account_status\":\"VERIFIED\",\"name\":{\"given_name\":\"John\",\"surname\":\"Doe\"},\"address\":{\"country_code\":\"PH\"}}},\"purchase_units\":[{\"reference_id\":\"default\",\"payments\":{\"captures\":[{\"id\":\"1VK16968M2684913B\",\"status\":\"COMPLETED\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"11.00\"},\"final_capture\":true,\"seller_protection\":{\"status\":\"ELIGIBLE\",\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"]},\"seller_receivable_breakdown\":{\"gross_amount\":{\"currency_code\":\"PHP\",\"value\":\"11.00\"},\"paypal_fee\":{\"currency_code\":\"PHP\",\"value\":\"11.00\"},\"net_amount\":{\"currency_code\":\"PHP\",\"value\":\"0.00\"}},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/1VK16968M2684913B\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/1VK16968M2684913B\\/refund\",\"rel\":\"refund\",\"method\":\"POST\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/6H799260H9019635L\",\"rel\":\"up\",\"method\":\"GET\"}],\"create_time\":\"2026-01-08T10:21:44Z\",\"update_time\":\"2026-01-08T10:21:44Z\"}]}}],\"payer\":{\"name\":{\"given_name\":\"John\",\"surname\":\"Doe\"},\"email_address\":\"sb-ooty948571342@personal.example.com\",\"payer_id\":\"PPK5U4F2HK5LS\",\"address\":{\"country_code\":\"PH\"}},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/6H799260H9019635L\",\"rel\":\"self\",\"method\":\"GET\"}]}', '2026-01-08 10:22:07'),
(17, 24, 'PayPal', '5XW99134M14163949', '6KC926861L180743V', 'captured', '{\"id\":\"5XW99134M14163949\",\"status\":\"COMPLETED\",\"payment_source\":{\"paypal\":{\"email_address\":\"sb-ooty948571342@personal.example.com\",\"account_id\":\"PPK5U4F2HK5LS\",\"account_status\":\"VERIFIED\",\"name\":{\"given_name\":\"John\",\"surname\":\"Doe\"},\"address\":{\"country_code\":\"PH\"}}},\"purchase_units\":[{\"reference_id\":\"default\",\"payments\":{\"captures\":[{\"id\":\"6KC926861L180743V\",\"status\":\"COMPLETED\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"4999.00\"},\"final_capture\":true,\"seller_protection\":{\"status\":\"ELIGIBLE\",\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"]},\"seller_receivable_breakdown\":{\"gross_amount\":{\"currency_code\":\"PHP\",\"value\":\"4999.00\"},\"paypal_fee\":{\"currency_code\":\"PHP\",\"value\":\"184.97\"},\"net_amount\":{\"currency_code\":\"PHP\",\"value\":\"4814.03\"}},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/6KC926861L180743V\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/6KC926861L180743V\\/refund\",\"rel\":\"refund\",\"method\":\"POST\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/5XW99134M14163949\",\"rel\":\"up\",\"method\":\"GET\"}],\"create_time\":\"2026-01-08T12:59:14Z\",\"update_time\":\"2026-01-08T12:59:14Z\"}]}}],\"payer\":{\"name\":{\"given_name\":\"John\",\"surname\":\"Doe\"},\"email_address\":\"sb-ooty948571342@personal.example.com\",\"payer_id\":\"PPK5U4F2HK5LS\",\"address\":{\"country_code\":\"PH\"}},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/5XW99134M14163949\",\"rel\":\"self\",\"method\":\"GET\"}]}', '2026-01-08 12:59:38');

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
(1, 'NK-AF1-001', 'AIR FORCE 1', 'Nike', 'Men', 'White/White', 'Legendary leather icon with everyday cushioning.', '2024-01-15', 'assets/img/products/air-force-1.png', 4999.00, 19, 1, 0, 'active', '2026-01-03 14:28:54', 'Lifestyle', 'Women'),
(2, 'AD-GAZ-IND', 'GAZELLE INDOOR', 'Adidas', 'Men', 'Blue Fusion/White', '1979 indoor classic with soft suede and gum tooling.', '2024-03-10', 'assets/img/products/adidas-gazelle-indoor.jpg', 4700.00, 14, 0, 0, 'active', '2026-01-03 14:28:54', 'Lifestyle', 'None'),
(3, 'AS-GK14-001', 'GEL-KAYANO 14', 'Asics', 'Men', 'Cream/Pure Silver', 'Retro runner revived with GEL cushioning.', '2024-02-20', 'assets/img/products/asics-gel-kayano-14.png', 9490.00, 10, 0, 0, 'active', '2026-01-03 14:28:54', 'Running', 'Women'),
(4, 'PM-SUE-CLS', 'SUEDE CLASSIC', 'Puma', 'Men', 'Black/White', 'Street staple since 1968 with soft suede upper.', '2023-11-05', 'assets/img/products/puma-suede-classic.png', 3999.00, 21, 0, 0, 'active', '2026-01-03 14:28:54', 'Lifestyle', 'None'),
(5, 'CT8012-116', 'Jordan 11 Retro Legend Blue', 'Jordan', 'Men', 'White/Legend Blue/Black', 'Patent mudguard shine with icy outsole.', '2024-12-13', 'assets/img/products/jordan-11-legend-blue.png', 12000.00, 0, 1, 0, 'active', '2026-01-03 14:28:54', 'Basketball', 'None'),
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
(18, 'NK-SAB-1-W', 'SABRINA 1 W', 'Nike', 'Women', 'Oxygen Purple/Black', 'Lightweight guard shoe tuned for quick cuts.', '2024-07-18', 'assets/img/products/nike-sabrina-1-w.png', 8900.00, 6, 0, 0, 'active', '2026-01-03 14:28:54', 'Basketball', 'None'),
(19, 'TE-TES-3B86CB', 'test', 'tesrt', 'Men', 'tesst', 'test', '2026-01-03', '', 11.00, 1, 0, 0, 'active', '2026-01-03 18:17:43', 'Running', 'Women');

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
(2, 1, 'US M 8', 'US', 'Men', 6, 1, '2026-01-03 14:28:54'),
(3, 1, 'US M 9', 'US', 'Men', 4, 1, '2026-01-03 14:28:54'),
(4, 1, 'US M 10', 'US', 'Men', 4, 1, '2026-01-03 14:28:54'),
(7, 2, 'US 6', 'US', 'Men', 1, 1, '2026-01-03 14:28:54'),
(8, 2, 'US 7', 'US', 'Men', 6, 1, '2026-01-03 14:28:54'),
(9, 2, 'US 8', 'US', 'Men', 5, 1, '2026-01-03 14:28:54'),
(10, 2, 'US 9', 'US', 'Men', 2, 1, '2026-01-03 14:28:54'),
(15, 3, 'US 10', 'US', 'Men', 0, 1, '2026-01-03 14:28:54'),
(16, 3, 'US 11', 'US', 'Men', 0, 1, '2026-01-03 14:28:54'),
(17, 4, 'US 7', 'US', 'Men', 6, 1, '2026-01-03 14:28:54'),
(18, 4, 'US 8', 'US', 'Men', 7, 1, '2026-01-03 14:28:54'),
(19, 4, 'US 9', 'US', 'Men', 5, 1, '2026-01-03 14:28:54'),
(20, 4, 'US 10', 'US', 'Men', 3, 1, '2026-01-03 14:28:54'),
(22, 5, 'US 9', 'US', 'Men', 0, 1, '2026-01-03 14:28:54'),
(23, 5, 'US 10', 'US', 'Men', 0, 1, '2026-01-03 14:28:54'),
(24, 5, 'US 11', 'US', 'Men', 0, 1, '2026-01-03 14:28:54'),
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
(230, 1, 'US 10', 'US', 'Men', 0, 1, '2026-01-03 15:18:53'),
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
(253, 3, 'US 8.5', 'US', 'Men', 1, 1, '2026-01-03 15:18:53'),
(254, 3, 'US 9', 'US', 'Men', 2, 1, '2026-01-03 15:18:53'),
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
(488, 3, 'US W 7', 'US', 'Both', 1, 1, '2026-01-03 17:58:04'),
(489, 19, '7', 'US', 'Men', 1, 1, '2026-01-05 06:58:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
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

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `birthdate`, `gender`, `is_active`, `created_at`) VALUES
(1, 'Admin User', 'admin@solesource.com', '', '$2y$10$qANV1OhHZOkNsyAxVeqdzuJIBDgo7Vv3UQCNoArGVXo8y0pV3pAqe', 'admin', '2026-01-06', 'Male', 1, '2026-01-03 03:06:06'),
(2, 'James Carlo', 'james@solesource.com', '', '$2y$10$iHmLr9cKN.4P/ptOhCZAC.v/S9ASrMFxpHfFlCM3vnAGunFQouNHy', 'customer', '2005-06-10', 'Male', 1, '2026-01-03 03:24:15'),
(3, 'Laurence Adrian Caranay', 'laurenceadrian1@gmail.com', '', '$2y$10$OeGBBULUOoAjPief1Tw/ButEeWuZn8wfJkFMWatZ.p4.sE6mjKNQ.', 'customer', NULL, NULL, 1, '2026-01-05 01:46:43'),
(4, 'Zakeesha Elisha Canubas', 'zakeeshaelishacanubas@gmail.com', '', '$2y$10$KvhGJNKZkixa7B55llAlDeyQVfEi7NznBmNGL9KbUiFifVeaU3OM2', 'customer', NULL, NULL, 1, '2026-01-05 08:32:32'),
(5, 'james calro', 'avera.visuals@gmail.com', '09457996892', '$2y$10$3.aHCM5Sbfhy/F0riQ2Z1O7pm9dD9OGyspysyTCn3cDxumgshYMkO', 'customer', NULL, NULL, 1, '2026-01-05 23:29:39'),
(6, 'avera', 'avear@solesource.com', '09457996892', '$2y$10$SkTdhAuBhZoPJp5C8b27B.tx0Xgb5vaVhwS9r/ALrXunCmyhgrEf.', 'customer', NULL, NULL, 1, '2026-01-05 23:31:16'),
(7, 'James Rivera', 'jamescarlorivera52@gmail.com', '09457996892', '$2y$10$VkL.1bzMuqbwUGOX73shwuTKkAUO4aWt9nkMzk4IkGHEuKXsdtkKC', 'customer', '2005-06-09', 'Male', 1, '2026-01-06 12:29:15'),
(8, 'test account', 'test@solesource.com', '09457996892', '$2y$10$UBZWeK8Wcm01Zm7SwNeXkuSaKZqJ8mEDwTkPM6Uob.IkZV7.bjyG.', 'customer', NULL, NULL, 1, '2026-01-08 01:23:43'),
(9, 'Paul Pancho', 'paulanthonypancho811@gmail.com', '09241001726', '$2y$10$aITaTm8grJDZdqJ3ZcMcEuCmP28feHk9Hj4RgtxSa9AM/rPdppF7O', 'customer', NULL, NULL, 1, '2026-01-09 04:45:28');

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
(2, 2, 'WORK', 'Admin User', '09457996892', 'BLK 27 LOT 25 WINE CUP ST', 'Itbayat', 'Batanes', 'Region II (Cagayan Valley)', 'Raele', '4294', 'Philippines', 1, '2026-01-03 05:43:20'),
(3, 7, '', 'James Carlo Rivera', '09457996892', 'BLK 27 LOT 25 WINE CUP STREET', 'Santo Tomas', 'Batangas', 'Region IV-A (CALABARZON)', 'Barangay II (Pob.)', '4234', 'Philippines', 1, '2026-01-09 03:00:40');

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
(3, 2, 1, '2026-01-09 03:46:13');

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
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

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
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_provider_order` (`provider_order_id`),
  ADD KEY `idx_provider_capture` (`provider_capture_id`);

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
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=490;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_wishlist`
--
ALTER TABLE `user_wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `fk_payment_transactions_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

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
