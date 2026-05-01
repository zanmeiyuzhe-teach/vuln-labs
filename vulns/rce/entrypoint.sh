#!/bin/bash

# Start MySQL
service mariadb start
sleep 2

# Create database and seed data
mysql -u root <<'SQL'
CREATE DATABASE IF NOT EXISTS cyberrange;
USE cyberrange;

CREATE TABLE IF NOT EXISTS flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flag VARCHAR(100) NOT NULL,
    description VARCHAR(200)
);

-- Seed flags for each difficulty
INSERT INTO flags (flag, description) VALUES
('CR{rce_ping_easy}', 'RCE Easy - Ping command execution'),
('CR{rce_command_concat}', 'RCE Simple - Command concatenation bypass'),
('CR{rce_eval_code}', 'RCE Hard - eval() code execution'),
('CR{rce_system_pwn}', 'RCE Hell - System command + privilege escalation');

-- Create restricted user
CREATE USER IF NOT EXISTS 'cyberrange'@'localhost' IDENTIFIED BY 'cr_lab_pass';
GRANT SELECT ON cyberrange.* TO 'cyberrange'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "[*] RCE Lab database initialized"

# Create index page
cat > /var/www/html/index.php <<'INDEX'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>RCE Lab</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #10b981; margin-bottom: 1rem; }
        .cards { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 2rem; }
        .card {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 12px; padding: 1.5rem;
            text-decoration: none; color: #f5f5f5; transition: all 0.2s;
        }
        .card:hover { border-color: #10b981; transform: translateY(-2px); }
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
        <h1>命令执行靶场</h1>
        <p style="color: #a1a1aa;">选择难度级别开始练习</p>
        <div class="cards">
            <a href="/easy/" class="card">
                <span class="badge easy">Easy</span>
                <h3 style="margin-top: 0.5rem;">Ping 执行</h3>
                <p>Ping 功能直接执行系统命令，无任何过滤</p>
            </a>
            <a href="/simple/" class="card">
                <span class="badge simple">Simple</span>
                <h3 style="margin-top: 0.5rem;">命令拼接绕过</h3>
                <p>有简单过滤，需要使用命令拼接技术绕过</p>
            </a>
            <a href="/hard/" class="card">
                <span class="badge hard">Hard</span>
                <h3 style="margin-top: 0.5rem;">eval 代码执行</h3>
                <p>PHP eval() 函数导致的代码执行漏洞</p>
            </a>
            <a href="/hell/" class="card">
                <span class="badge hell">Hell</span>
                <h3 style="margin-top: 0.5rem;">综合 RCE</h3>
                <p>需要多种技术组合实现远程命令执行</p>
            </a>
        </div>
    </div>
</body>
</html>
INDEX

echo "[*] Index page created"

# Start Apache
apache2-foreground
