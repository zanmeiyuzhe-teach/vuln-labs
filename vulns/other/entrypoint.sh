#!/bin/bash

# Start MySQL
service mariadb start
sleep 2

# Create database and seed data
mysql -u root <<'SQL'
CREATE DATABASE IF NOT EXISTS cyberrange;
USE cyberrange;

CREATE TABLE IF NOT EXISTS secrets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    secret_key VARCHAR(50) NOT NULL,
    secret_value TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flag VARCHAR(100) NOT NULL,
    description VARCHAR(200)
);

-- Seed secrets
INSERT INTO secrets (secret_key, secret_value) VALUES
('api_key', 'sk-1234567890abcdef'),
('db_password', 'super_secret_password'),
('admin_token', 'CR{info_disclosure_easy}');

-- Seed flags
INSERT INTO flags (flag, description) VALUES
('CR{directory_traversal}', 'Other Easy - Directory traversal'),
('CR{info_disclosure}', 'Other Simple - Information disclosure'),
('CR{deserialization}', 'Other Hard - PHP deserialization'),
('CR{ssrf_chain}', 'Other Hell - SSRF + URL redirect chain');

-- Create restricted user
CREATE USER IF NOT EXISTS 'cyberrange'@'localhost' IDENTIFIED BY 'cr_lab_pass';
GRANT SELECT ON cyberrange.* TO 'cyberrange'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "[*] Other Lab database initialized"

# Create sensitive files for directory traversal
echo "CR{directory_traversal_easy}" > /var/www/html/flag.txt
echo "Database credentials: root / toor" > /var/www/html/config.txt
mkdir -p /var/www/html/.git
echo "Git repository found!" > /var/www/html/.git/HEAD

# Create index page
cat > /var/www/html/index.php <<'INDEX'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Other Vulnerabilities Lab</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #6b7280; margin-bottom: 1rem; }
        .cards { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 2rem; }
        .card {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 12px; padding: 1.5rem;
            text-decoration: none; color: #f5f5f5; transition: all 0.2s;
        }
        .card:hover { border-color: #6b7280; transform: translateY(-2px); }
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
        <h1>其他高频漏洞靶场</h1>
        <p style="color: #a1a1aa;">选择难度级别开始练习</p>
        <div class="cards">
            <a href="/easy/" class="card">
                <span class="badge easy">Easy</span>
                <h3 style="margin-top: 0.5rem;">目录遍历</h3>
                <p>通过路径遍历访问敏感文件</p>
            </a>
            <a href="/simple/" class="card">
                <span class="badge simple">Simple</span>
                <h3 style="margin-top: 0.5rem;">信息泄露</h3>
                <p>服务器配置错误导致敏感信息暴露</p>
            </a>
            <a href="/hard/" class="card">
                <span class="badge hard">Hard</span>
                <h3 style="margin-top: 0.5rem;">PHP 反序列化</h3>
                <p>unserialize() 漏洞利用</p>
            </a>
            <a href="/hell/" class="card">
                <span class="badge hell">Hell</span>
                <h3 style="margin-top: 0.5rem;">SSRF + URL 重定向</h3>
                <p>服务端请求伪造与 URL 重定向链</p>
            </a>
        </div>
    </div>
</body>
</html>
INDEX

echo "[*] Index page created"

# Start Apache
apache2-foreground
