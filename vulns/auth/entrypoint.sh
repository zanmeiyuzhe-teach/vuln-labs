#!/bin/bash

# Create index page
cat > /var/www/html/index.php <<'INDEX'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Auth Vulnerabilities Lab</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #ef4444; margin-bottom: 1rem; }
        .cards { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 2rem; }
        .card {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 12px; padding: 1.5rem;
            text-decoration: none; color: #f5f5f5; transition: all 0.2s;
        }
        .card:hover { border-color: #ef4444; transform: translateY(-2px); }
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
        <h1>暴力破解靶场</h1>
        <p style="color: #a1a1aa;">选择难度级别开始练习</p>
        <div class="cards">
            <a href="/easy/" class="card">
                <span class="badge easy">Easy</span>
                <h3 style="margin-top: 0.5rem;">表单爆破入门</h3>
                <p>无防护的登录表单，直接暴力破解</p>
            </a>
            <a href="/simple/" class="card">
                <span class="badge simple">Simple</span>
                <h3 style="margin-top: 0.5rem;">验证码绕过</h3>
                <p>有验证码但实现有缺陷</p>
            </a>
            <a href="/hard/" class="card">
                <span class="badge hard">Hard</span>
                <h3 style="margin-top: 0.5rem;">Token 防爆破</h3>
                <p>有 CSRF Token 但验证不严格</p>
            </a>
            <a href="/hell/" class="card">
                <span class="badge hell">Hell</span>
                <h3 style="margin-top: 0.5rem;">综合防御绕过</h3>
                <p>验证码 + Token + 锁定多层防御</p>
            </a>
        </div>
    </div>
</body>
</html>
INDEX

echo "[*] Auth Lab index page created"

# Start Apache
apache2-foreground
