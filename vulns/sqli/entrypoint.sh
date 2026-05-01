#!/bin/bash

# Start MySQL
service mariadb start
sleep 2

# Create database and seed data
mysql -u root <<'SQL'
CREATE DATABASE IF NOT EXISTS cyberrange;
USE cyberrange;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    category VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flag VARCHAR(100) NOT NULL,
    description VARCHAR(200)
);

-- Seed users (passwords are intentionally weak for the lab)
INSERT INTO users (username, password, email, role) VALUES
('admin', 'admin123', 'admin@cyberrange.local', 'admin'),
('user1', 'password1', 'user1@cyberrange.local', 'user'),
('user2', 'letmein', 'user2@cyberrange.local', 'user'),
('test', 'test123', 'test@cyberrange.local', 'user');

-- Seed products
INSERT INTO products (name, price, description, category) VALUES
('Wireless Mouse', 29.99, 'Ergonomic wireless mouse with USB receiver', 'Electronics'),
('Mechanical Keyboard', 89.99, 'RGB mechanical keyboard with Cherry MX switches', 'Electronics'),
('USB-C Hub', 45.99, '7-in-1 USB-C hub with HDMI output', 'Electronics'),
('Webcam HD', 59.99, '1080p HD webcam with built-in microphone', 'Electronics'),
('Monitor Stand', 34.99, 'Adjustable monitor stand with USB ports', 'Accessories'),
('Desk Lamp', 24.99, 'LED desk lamp with brightness control', 'Accessories'),
('Headphones', 79.99, 'Noise-cancelling over-ear headphones', 'Audio'),
('Speaker', 49.99, 'Bluetooth portable speaker', 'Audio');

-- Seed flag
INSERT INTO flags (flag, description) VALUES
('CR{sqli_numeric_injection_success}', 'SQL Injection Easy - Numeric type injection flag');

-- Create a restricted user for the lab
CREATE USER IF NOT EXISTS 'cyberrange'@'localhost' IDENTIFIED BY 'cr_lab_pass';
GRANT SELECT, INSERT, UPDATE ON cyberrange.* TO 'cyberrange'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "[*] Database initialized successfully"

# Start Apache
apache2-foreground
