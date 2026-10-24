SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS webphones CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE webphones;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    category VARCHAR(80) NOT NULL DEFAULT 'Điện thoại',
    price DECIMAL(12,2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image_url VARCHAR(1000) NOT NULL DEFAULT '',
    description TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE products
    ADD COLUMN IF NOT EXISTS category VARCHAR(80) NOT NULL DEFAULT 'Điện thoại' AFTER brand;

CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NOT NULL
) ENGINE=InnoDB;

INSERT INTO site_settings (setting_key, setting_value)
VALUES ('announcement_ticker', 'Ưu đãi công nghệ hấp dẫn mỗi ngày · Giao hàng toàn quốc · Hỗ trợ tư vấn tận tâm')
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    phone VARCHAR(40) NOT NULL DEFAULT '',
    address VARCHAR(500) NOT NULL DEFAULT '',
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NULL,
    customer_name VARCHAR(180) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    email VARCHAR(180) NOT NULL,
    address VARCHAR(500) NOT NULL,
    note TEXT NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'Chờ xác nhận',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS customer_id INT UNSIGNED NULL AFTER id;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    product_name VARCHAR(180) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    email VARCHAR(180) NOT NULL,
    subject VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO admins (username, password_hash)
VALUES ('admin', '$2y$10$UAh7ZNwgXfJw6XuTsfonTO4ZdankkTFBCXKvQw8P.yBSIfNUJ/5Da');

INSERT INTO products (name, brand, price, stock, image_url, description)
SELECT 'iPhone 15', 'Apple', 19990000, 12, 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?auto=format&fit=crop&w=900&q=85', 'Thiết kế thanh lịch, camera độ phân giải cao và hiệu năng mạnh mẽ cho mọi nhu cầu hằng ngày.'
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'iPhone 15');
INSERT INTO products (name, brand, price, stock, image_url, description)
SELECT 'Samsung Galaxy S24', 'Samsung', 18990000, 8, 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?auto=format&fit=crop&w=900&q=85', 'Màn hình sống động, camera linh hoạt và trải nghiệm thông minh trong thiết kế cao cấp.'
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Samsung Galaxy S24');
INSERT INTO products (name, brand, price, stock, image_url, description)
SELECT 'Xiaomi 14', 'Xiaomi', 15990000, 10, 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=900&q=85', 'Hiệu năng ấn tượng, sạc nhanh tiện lợi và camera chất lượng trong một thiết kế gọn gàng.'
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Xiaomi 14');

UPDATE products
SET category = 'Máy tính bảng'
WHERE category = 'Điện thoại'
  AND (name LIKE '%Tab %' OR name LIKE '%Tablet%' OR name LIKE '%iPad%' OR name LIKE '%Pad %');

UPDATE products
SET description = 'Thiết kế thanh lịch, camera độ phân giải cao và hiệu năng mạnh mẽ cho mọi nhu cầu hằng ngày.'
WHERE name = 'iPhone 15' AND description LIKE '%?%';

UPDATE products
SET description = 'Màn hình sống động, camera linh hoạt và trải nghiệm thông minh trong thiết kế cao cấp.'
WHERE name = 'Samsung Galaxy S24' AND description LIKE '%?%';

UPDATE products
SET description = 'Hiệu năng ấn tượng, sạc nhanh tiện lợi và camera chất lượng trong một thiết kế gọn gàng.'
WHERE name = 'Xiaomi 14' AND description LIKE '%?%';

ALTER TABLE orders
    MODIFY status VARCHAR(40) NOT NULL DEFAULT 'Chờ xác nhận';
