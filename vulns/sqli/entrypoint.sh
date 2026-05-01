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

-- Seed flags for each difficulty
INSERT INTO flags (flag, description) VALUES
('CR{sqli_numeric_injection_success}', 'SQL Injection Easy - Numeric type injection flag'),
('CR{sqli_string_injection}', 'SQL Injection Simple - String type injection flag'),
('CR{sqli_blind_injection}', 'SQL Injection Hard - Blind injection flag'),
('CR{sqli_waf_bypass}', 'SQL Injection Hell - WAF bypass flag');

-- Create a restricted user for the lab
CREATE USER IF NOT EXISTS 'cyberrange'@'localhost' IDENTIFIED BY 'cr_lab_pass';
GRANT SELECT, INSERT, UPDATE ON cyberrange.* TO 'cyberrange'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "[*] Database initialized successfully"

# Create index page that links to all difficulties
cat > /var/www/html/index.php <<'INDEX'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>SQL Injection Lab</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #3b82f6; margin-bottom: 1rem; }
        .cards { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 2rem; }
        .card {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 12px; padding: 1.5rem;
            text-decoration: none; color: #f5f5f5; transition: all 0.2s;
        }
        .card:hover { border-color: #3b82f6; transform: translateY(-2px); }
        .card h3 { margin-bottom: 0.5rem; }
        .card p { color: #a1a1aa; font-size: 0.85rem; }
        .badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .easy { background: #10b981; color: white; }
        .simple { background: #3b82f6; color: white; }
        .hard { background: #f59e0b; color: white; }
        .hell { background: #ef4444; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>SQL 注入靶场</h1>
        <p style="color: #a1a1aa;">选择难度级别开始练习</p>
        <div class="cards">
            <a href="/easy/" class="card">
                <span class="badge easy">Easy</span>
                <h3 style="margin-top: 0.5rem;">数字型注入</h3>
                <p>产品查询页面，数字参数直接拼接</p>
            </a>
            <a href="/simple/" class="card">
                <span class="badge simple">Simple</span>
                <h3 style="margin-top: 0.5rem;">字符型注入</h3>
                <p>用户搜索功能，字符串参数拼接</p>
            </a>
            <a href="/hard/" class="card">
                <span class="badge hard">Hard</span>
                <h3 style="margin-top: 0.5rem;">布尔盲注</h3>
                <p>产品验证系统，只返回存在/不存在</p>
            </a>
            <a href="/hell/" class="card">
                <span class="badge hell">Hell</span>
                <h3 style="margin-top: 0.5rem;">WAF 绕过</h3>
                <p>有 WAF 保护的登录系统</p>
            </a>
        </div>
    </div>
</body>
</html>
INDEX

echo "[*] Index page created"

# Start Apache
apache2-foreground
