CREATE DATABASE IF NOT EXISTS thrift_pos;
USE thrift_pos;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    fullname VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    theme ENUM('light', 'dark') DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    gender ENUM('women', 'men', 'unisex') NOT NULL DEFAULT 'unisex',
    price DECIMAL(10, 2) NOT NULL,
    tag_color ENUM('red', 'blue', 'green', 'yellow') NOT NULL,
    image_url VARCHAR(255),
    status ENUM('available', 'sold', 'reserved') DEFAULT 'available',
    batch_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    status ENUM('paid', 'pending', 'cancelled') NOT NULL DEFAULT 'paid',
    cash_received DECIMAL(10, 2),
    `change` DECIMAL(10, 2),
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    item_id INT,
    price DECIMAL(10, 2) NOT NULL,
    discount DECIMAL(10, 2) DEFAULT 0,
    final_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);

CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT,
    customer_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20),
    quantity INT NOT NULL DEFAULT 1,
    notes TEXT,
    duration_days INT DEFAULT 1,
    expiration_date DATETIME,
    status ENUM('reserved', 'pending', 'paid', 'completed', 'cancelled', 'expired') NOT NULL DEFAULT 'reserved',
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id)
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE,
    setting_value VARCHAR(255)
);

-- Seed tag discounts in settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('discount_red', '0.50'),
('discount_blue', '0.30'),
('discount_green', '0.20'),
('discount_yellow', '0.00');

CREATE TABLE IF NOT EXISTS rack_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    price DECIMAL(10, 2) NOT NULL,
    gender ENUM('women', 'men', 'unisex') NOT NULL DEFAULT 'unisex',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed rack categories with fixed prices
INSERT INTO rack_categories (name, price, gender) VALUES 
('Printed Shirts', 150.00, 'unisex'),
('Branded Shirts', 300.00, 'unisex'),
('Pants', 250.00, 'unisex'),
('Jackets', 400.00, 'unisex'),
('Dresses', 350.00, 'women'),
('Skirts', 200.00, 'women'),
('Tops', 120.00, 'unisex'),
('Bottoms', 180.00, 'unisex'),
('Outerwear', 450.00, 'unisex'),
('Footwear', 350.00, 'unisex'),
('Accessories', 100.00, 'unisex');

-- Seed categories for rack-based pricing
INSERT INTO categories (name, price) VALUES 
('Printed Shirts', 150.00),
('Branded Shirts', 300.00),
('Pants', 250.00),
('Jackets', 400.00);
