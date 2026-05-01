#!/bin/bash

# Start MySQL
service mariadb start
sleep 2

# Create database and seed data
mysql -u root <<'SQL'
CREATE DATABASE IF NOT EXISTS cyberrange;
USE cyberrange;

CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flag VARCHAR(100) NOT NULL,
    description VARCHAR(200)
);

-- Seed flags
INSERT INTO flags (flag, description) VALUES
('CR{file_upload_easy}', 'File Upload Easy - Client-side bypass'),
('CR{file_upload_simple}', 'File Upload Simple - MIME type bypass'),
('CR{file_upload_hard}', 'File Upload Hard - Image header bypass'),
('CR{file_upload_hell}', 'File Upload Hell - Upload + getshell');

-- Create restricted user
CREATE USER IF NOT EXISTS 'cyberrange'@'localhost' IDENTIFIED BY 'cr_lab_pass';
GRANT SELECT, INSERT ON cyberrange.* TO 'cyberrange'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "[*] File Upload Lab database initialized"

# Create index page
cat > /var/www/html/index.php <<'INDEX'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>File Upload Lab</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #ec4899; margin-bottom: 1rem; }
        .cards { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 2rem; }
        .card {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 12px; padding: 1.5rem;
            text-decoration: none; color: #f5f5f5; transition: all 0.2s;
        }
        .card:hover { border-color: #ec4899; transform: translateY(-2px); }
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
        <h1>文件上传靶场</h1>
        <p style="color: #a1a1aa;">选择难度级别开始练习</p>
        <div class="cards">
            <a href="/easy/" class="card">
                <span class="badge easy">Easy</span>
                <h3 style="margin-top: 0.5rem;">客户端校验绕过</h3>
                <p>只有前端 JavaScript 验证，可直接绕过</p>
            </a>
            <a href="/simple/" class="card">
                <span class="badge simple">Simple</span>
                <h3 style="margin-top: 0.5rem;">MIME 类型绕过</h3>
                <p>检查 Content-Type，可伪造类型</p>
            </a>
            <a href="/hard/" class="card">
                <span class="badge hard">Hard</span>
                <h3 style="margin-top: 0.5rem;">图片头绕过</h3>
                <p>检查文件头，需要添加图片头绕过</p>
            </a>
            <a href="/hell/" class="card">
                <span class="badge hell">Hell</span>
                <h3 style="margin-top: 0.5rem;">上传 Getshell</h3>
                <p>综合绕过技术，上传 WebShell 获取权限</p>
            </a>
        </div>
    </div>
</body>
</html>
INDEX

echo "[*] Index page created"

# Start Apache
apache2-foreground
