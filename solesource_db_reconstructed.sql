-- Reconstructed schema inferred from application code (Jan 2026)
-- Safe to import on a fresh database. Review before running on production data.

CREATE DATABASE IF NOT EXISTS solesource_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;
USE solesource_db;

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- Drop existing tables in dependency order
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS user_addresses;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS user_wishlist;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS password_resets;

-- Users: supports auth + roles (admin/customer) and optional profile fields
CREATE TABLE users (
  id INT NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
  birthdate DATE NULL,
  gender VARCHAR(50) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Saved addresses per user (profile + checkout)
CREATE TABLE user_addresses (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  label VARCHAR(100) DEFAULT NULL,
  full_name VARCHAR(255) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  address_line VARCHAR(255) NOT NULL,
  city VARCHAR(255) DEFAULT NULL,
  province VARCHAR(255) DEFAULT NULL,
  region VARCHAR(255) DEFAULT NULL,
  barangay VARCHAR(255) DEFAULT NULL,
  zip_code VARCHAR(20) DEFAULT NULL,
  country VARCHAR(100) DEFAULT 'Philippines',
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_user_addresses_user (user_id),
  KEY idx_user_addresses_default (is_default),
  CONSTRAINT fk_user_addresses_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products: matches admin add-product form and storefront reads
CREATE TABLE products (
  id INT NOT NULL AUTO_INCREMENT,
  sku VARCHAR(64) NOT NULL,
  name VARCHAR(255) NOT NULL,
  brand VARCHAR(100) NOT NULL,
  gender VARCHAR(50) NOT NULL DEFAULT 'Unisex',
  colorway VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  release_date DATE DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  price DECIMAL(12,2) NOT NULL,
  stock_quantity INT NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  total_sold INT NOT NULL DEFAULT 0,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_products_sku (sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders: captures checkout fields + status lifecycle
CREATE TABLE orders (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  order_number VARCHAR(64) NOT NULL,
  total_amount DECIMAL(12,2) NOT NULL,
  payment_method VARCHAR(50) NOT NULL DEFAULT 'COD',
  phone VARCHAR(50) DEFAULT NULL,
  full_name VARCHAR(255) NOT NULL,
  address VARCHAR(255) DEFAULT NULL,
  city VARCHAR(255) DEFAULT NULL,
  province VARCHAR(255) DEFAULT NULL,
  region VARCHAR(255) DEFAULT NULL,
  barangay VARCHAR(255) DEFAULT NULL,
  zip_code VARCHAR(20) DEFAULT NULL,
  country VARCHAR(100) DEFAULT 'Philippines',
  shipping_address TEXT DEFAULT NULL,
  tracking_number VARCHAR(100) DEFAULT NULL,
  courier VARCHAR(100) DEFAULT NULL,
  status ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_order_number (order_number),
  KEY idx_orders_user (user_id),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order line items: one row per product/size per order
CREATE TABLE order_items (
  id INT NOT NULL AUTO_INCREMENT,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  size VARCHAR(50) DEFAULT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price_at_purchase DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_order_items_order (order_id),
  KEY idx_order_items_product (product_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User wishlist (one row per user/product)
CREATE TABLE user_wishlist (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_product (user_id, product_id),
  KEY idx_wishlist_user (user_id),
  KEY idx_wishlist_product (product_id),
  CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin logs (present in legacy dump; kept for compatibility)
CREATE TABLE admin_logs (
  id INT NOT NULL AUTO_INCREMENT,
  admin_id INT NOT NULL,
  action VARCHAR(255) NOT NULL,
  details TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_admin_logs_admin (admin_id),
  CONSTRAINT fk_admin_logs_user FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password reset tokens (not used yet in code but kept from old dump)
CREATE TABLE password_resets (
  id INT NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: seed an admin user (password = Admin123!)
-- INSERT INTO users (full_name, email, password, role) VALUES (
--   'Store Admin', 'admin@example.com',
--   '$2y$10$lSmOZQ7n9pfWq3ApdGUmPuWYo5tsL8hIGVv6QbYtSzbw9R1ZuCjyS',
--   'admin'
-- );


DELETE FROM products;
INSERT INTO products (sku, name, brand, gender, colorway, description, release_date, image, price, stock_quantity, is_featured, total_sold, status) VALUES
('NK-AF1-001', 'AIR FORCE 1', 'Nike', 'Unisex', 'White/White', 'The legend lives on in the Nike Air Force 1.', '2024-01-15', 'assets/img/products/air-force-1.png', 4999.00, 25, 1, 0, 'active'),
('AD-GAZ-IND', 'GAZELLE INDOOR', 'Adidas', 'Unisex', 'Blue Fusion/White', 'Reviving a 1979 classic with premium suede.', '2024-03-10', 'assets/img/products/adidas-gazelle-indoor.jpg', 4700.00, 20, 0, 0, 'active'),
('AS-GK14-001', 'GEL-KAYANO 14', 'Asics', 'Men', 'Cream/Pure Silver', 'Late 2000s aesthetic with retro running shape.', '2024-02-20', 'assets/img/products/asics-gel-kayano-14.png', 9490.00, 15, 0, 0, 'active'),
('PM-SUE-CLS', 'SUEDE CLASSIC', 'Puma', 'Unisex', 'Black/White', 'Game-changing suede icon since 1968.', '2023-11-05', 'assets/img/products/puma-suede-classic.png', 3999.00, 30, 0, 0, 'active'),
('CT8012-116', 'Jordan 11 Retro Legend Blue', 'Jordan', 'Men', 'White/Legend Blue/Black', 'Patent mudguard with Legend Blue hits.', '2024-12-13', 'assets/img/products/jordan-11-legend-blue.png', 12000.00, 8, 1, 0, 'active'),
('AD-SAM-OG', 'SAMBA OG', 'Adidas', 'Unisex', 'Cloud White/Core Black', 'Timeless icon with soft leather upper.', '2024-01-05', 'assets/img/products/adidas-samba-og.png', 5200.00, 18, 0, 0, 'active'),
('AS-GL3-001', 'GEL-LYTE III', 'Asics', 'Men', 'Grey/Black', 'Famous split-tongue runner from the 90s.', '2023-12-15', 'assets/img/products/asics-gel-lyte-iii.png', 7990.00, 12, 0, 0, 'active'),
('PM-RSX-001', 'RS-X', 'Puma', 'Unisex', 'White/Royal/Red', 'Bulky silhouette with retro palette.', '2024-04-01', 'assets/img/products/puma-rsx.png', 5499.00, 16, 0, 0, 'active'),
('NK-DNK-LOW', 'DUNK LOW', 'Nike', 'Unisex', 'Black/White', 'Crisp overlays and original team colors.', '2024-02-28', 'assets/img/products/nike-dunk-low.png', 5795.00, 22, 1, 0, 'active'),
('AD-SUP-STAR', 'SUPERSTAR', 'Adidas', 'Unisex', 'White/Black', 'Shell-toe icon from court to stage.', '2023-10-20', 'assets/img/products/adidas-superstar.png', 4500.00, 28, 0, 0, 'active'),
('AS-GT2-160', 'GT-2160', 'Asics', 'Men', 'White/Illusion Blue', 'GT-2000 series homage with tech language.', '2024-03-25', 'assets/img/products/asics-gt-2160.png', 6890.00, 14, 0, 0, 'active'),
('NK-BLZ-MID', 'BLAZER MID', 'Nike', 'Unisex', 'White/Black', '70s hardwood classic with vintage midsole.', '2023-09-10', 'assets/img/products/nike-blazer-mid.png', 5295.00, 19, 0, 0, 'active');
