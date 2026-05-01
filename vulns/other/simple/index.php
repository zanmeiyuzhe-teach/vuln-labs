<?php
$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Simple - 信息泄露</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #3b82f6; margin-bottom: 0.5rem; }
        .subtitle { color: #a1a1aa; margin-bottom: 2rem; font-size: 0.9rem; }
        .hint { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .hint h3 { color: #3b82f6; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .hint p { color: #a1a1aa; font-size: 0.85rem; }
        .hint code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .content {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .content h2 { margin-bottom: 1rem; }
        .content p { color: #a1a1aa; line-height: 1.6; }
        .error-page {
            background: #1a0000; border: 1px solid #3f0000; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .error-page h2 { color: #ef4444; margin-bottom: 1rem; }
        .error-page .trace { font-family: monospace; font-size: 0.85rem; color: #a1a1aa; margin-bottom: 1rem; }
        .error-page .env { color: #f59e0b; }
        .info { background: #0a0a1a; border: 1px solid #00003f; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #3b82f6; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Web 应用</h1>
        <p class="subtitle">演示信息泄露漏洞 | CyberRange Other Simple Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场演示了多种信息泄露场景，包括错误信息泄露、目录列表、配置文件暴露等。</p>
            <p>攻击者可以通过这些信息了解系统架构，为进一步攻击做准备。</p>
            <p>试试触发错误: <code>?page=nonexistent</code></p>
        </div>

        <?php
        switch ($page) {
            case 'home':
                echo '<div class="content">';
                echo '<h2>欢迎访问</h2>';
                echo '<p>这是一个演示信息泄露漏洞的靶场。</p>';
                echo '<p>尝试访问不同的页面，看看能发现什么信息。</p>';
                echo '</div>';
                break;

            case 'about':
                echo '<div class="content">';
                echo '<h2>关于我们</h2>';
                echo '<p>CyberRange - 网安基础学习靶场平台</p>';
                echo '<p>版本: 1.0.0</p>';
                echo '<p>服务器: Apache/2.4.57 (Ubuntu)</p>';
                echo '<p>PHP 版本: ' . phpversion() . '</p>';
                echo '</div>';
                break;

            case 'error':
                // VULNERABLE: Error page with stack trace and env vars
                echo '<div class="error-page">';
                echo '<h2>500 Internal Server Error</h2>';
                echo '<div class="trace">';
                echo 'Stack Trace:<br>';
                echo '#0 /var/www/html/index.php(42): require()<br>';
                echo '#1 /var/www/html/framework/router.php(128): Controller->handle()<br>';
                echo '#2 /var/www/html/framework/app.php(56): Router->dispatch()<br>';
                echo '</div>';
                echo '<div class="env">';
                echo '<strong>Environment Variables:</strong><br>';
                echo 'DB_HOST=localhost<br>';
                echo 'DB_USER=root<br>';
                echo 'DB_PASS=super_secret_password<br>';
                echo 'API_KEY=sk-1234567890abcdef<br>';
                echo 'SECRET_KEY=CR{info_disclosure_easy}<br>';
                echo '</div>';
                echo '</div>';
                break;

            case 'phpinfo':
                // VULNERABLE: phpinfo() exposure
                phpinfo();
                break;

            case 'robots':
                // VULNERABLE: robots.txt with sensitive paths
                header('Content-Type: text/plain');
                echo "User-agent: *\n";
                echo "Disallow: /admin/\n";
                echo "Disallow: /backup/\n";
                echo "Disallow: /config/\n";
                echo "Disallow: /database/\n";
                echo "Disallow: /.git/\n";
                echo "Disallow: /phpmyadmin/\n";
                exit;

            default:
                // VULNERABLE: Directory listing
                echo '<div class="content">';
                echo '<h2>文件列表</h2>';
                $files = scandir('.');
                foreach ($files as $f) {
                    if ($f !== '.' && $f !== '..') {
                        echo '<p>' . htmlspecialchars($f) . '</p>';
                    }
                }
                echo '</div>';
        }
        ?>

        <div class="info">
            <h3>信息泄露类型</h3>
            <p><strong>错误信息:</strong> 详细的错误堆栈暴露系统架构</p>
            <p><strong>phpinfo():</strong> 泄露 PHP 配置、环境变量、服务器信息</p>
            <p><strong>robots.txt:</strong> 暴露敏感目录路径</p>
            <p><strong>目录列表:</strong> 显示服务器上的文件结构</p>
            <p><strong>环境变量:</strong> 泄露数据库密码、API 密钥等</p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>
</body>
</html>
