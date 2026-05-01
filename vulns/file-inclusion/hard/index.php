<?php
$page = $_GET['page'] ?? 'home';

// Simple filter: remove ../ and null bytes
$page = str_replace('../', '', $page);
$page = str_replace('%00', '', $page);
$page = str_replace("\0", '', $page);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Inclusion Hard - 绕过</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #f59e0b; margin-bottom: 0.5rem; }
        .subtitle { color: #a1a1aa; margin-bottom: 2rem; font-size: 0.9rem; }
        .hint { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .hint h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .hint p { color: #a1a1aa; font-size: 0.85rem; }
        .hint code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .filter-info {
            background: #1a1a00; border: 1px solid #3f3f00; border-radius: 8px; padding: 1rem; margin-bottom: 2rem;
        }
        .filter-info h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .filter-info p { color: #a1a1aa; font-size: 0.85rem; }
        .nav { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .nav a {
            padding: 0.5rem 1rem; background: #27272a; color: #f5f5f5; text-decoration: none;
            border-radius: 8px; font-size: 0.9rem;
        }
        .nav a:hover { background: #3f3f46; }
        .nav a.active { background: #f59e0b; color: white; }
        .content {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem; min-height: 200px;
        }
        .content h2 { margin-bottom: 1rem; }
        .content p { color: #a1a1aa; line-height: 1.6; }
        .info { background: #1a0a00; border: 1px solid #3f2000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>小型网站 CMS</h1>
        <p class="subtitle">页面管理系统 (有过滤) | CyberRange File Inclusion Hard Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场有简单的过滤，移除了 <code>../</code> 和 null 字节。</p>
            <p>但过滤不完整，可以使用编码或双写绕过。</p>
            <p>试试: <code>..././..././..././etc/passwd</code> (双写绕过)</p>
        </div>

        <div class="filter-info">
            <h3>过滤规则</h3>
            <p>移除 <code>../</code> → 双写绕过: <code>..././</code></p>
            <p>移除 <code>%00</code> → 使用 <code>%2500</code> 或其他编码</p>
        </div>

        <div class="nav">
            <a href="?page=home" class="<?php echo $page === 'home' ? 'active' : ''; ?>">首页</a>
            <a href="?page=about" class="<?php echo $page === 'about' ? 'active' : ''; ?>">关于</a>
            <a href="?page=contact" class="<?php echo $page === 'contact' ? 'active' : ''; ?>">联系我们</a>
        </div>

        <div class="content">
            <?php
            $file = "pages/" . $page . ".php";
            if (file_exists($file)) {
                include($file);
            } else {
                include($page);
            }
            ?>
        </div>

        <div class="info">
            <h3>绕过技术</h3>
            <p><strong>双写绕过:</strong> <code>..././..././etc/passwd</code></p>
            <p>过滤移除一个 <code>../</code> 后，剩下的字符组成新的 <code>../</code></p>
            <p><strong>URL 编码:</strong> <code>%2e%2e%2f</code> (../ 的 URL 编码)</p>
            <p><strong>双重编码:</strong> <code>%252e%252e%252f</code></p>
            <p><strong>路径截断:</strong> <code>././././././././././etc/passwd</code></p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>
</body>
</html>
