<?php
$page = $_GET['page'] ?? 'home';

// VULNERABLE: Direct file inclusion without sanitization
// This allows Local File Inclusion (LFI)
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Inclusion Easy - LFI</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #10b981; margin-bottom: 0.5rem; }
        .subtitle { color: #a1a1aa; margin-bottom: 2rem; font-size: 0.9rem; }
        .hint { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .hint h3 { color: #10b981; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .hint p { color: #a1a1aa; font-size: 0.85rem; }
        .hint code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .nav { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .nav a {
            padding: 0.5rem 1rem; background: #27272a; color: #f5f5f5; text-decoration: none;
            border-radius: 8px; font-size: 0.9rem;
        }
        .nav a:hover { background: #3f3f46; }
        .nav a.active { background: #10b981; color: white; }
        .content {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem; min-height: 200px;
        }
        .content h2 { margin-bottom: 1rem; }
        .content p { color: #a1a1aa; line-height: 1.6; }
        .info { background: #0a1a0a; border: 1px solid #003f00; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #10b981; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>小型网站 CMS</h1>
        <p class="subtitle">页面管理系统 | CyberRange File Inclusion Easy Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个本地文件包含 (LFI) 靶场。网站使用 <code>include()</code> 函数加载页面。</p>
            <p>攻击者可以通过修改 <code>page</code> 参数包含任意本地文件。</p>
            <p>试试: <code>?page=../../../../etc/passwd</code></p>
        </div>

        <div class="nav">
            <a href="?page=home" class="<?php echo $page === 'home' ? 'active' : ''; ?>">首页</a>
            <a href="?page=about" class="<?php echo $page === 'about' ? 'active' : ''; ?>">关于</a>
            <a href="?page=contact" class="<?php echo $page === 'contact' ? 'active' : ''; ?>">联系我们</a>
        </div>

        <div class="content">
            <?php
            // VULNERABLE: Direct file inclusion
            $file = "pages/" . $page . ".php";

            if (file_exists($file)) {
                include($file);
            } else {
                // Try to include the raw path (allows path traversal)
                include($page);
            }
            ?>
        </div>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>代码直接将用户输入传递给 <code>include()</code> 函数：</p>
            <p><code>include("pages/" . $page . ".php");</code></p>
            <p>当文件不存在时，会尝试直接包含用户输入的路径：</p>
            <p><code>include($page);</code></p>
            <p>攻击者可以使用路径遍历包含任意文件：</p>
            <p><code>../../../../etc/passwd</code></p>
            <p><code>/etc/passwd</code> (绝对路径)</p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>
</body>
</html>
