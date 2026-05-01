<?php
$file = $_GET['file'] ?? '';
$content = '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Easy - 目录遍历</title>
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
        .file-list {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .file-list h3 { margin-bottom: 1rem; }
        .file-item {
            padding: 0.5rem 0; border-bottom: 1px solid #27272a; display: flex; justify-content: space-between;
        }
        .file-item:last-child { border-bottom: none; }
        .file-item .name { font-family: monospace; }
        .file-item .link { color: #10b981; text-decoration: none; font-size: 0.85rem; }
        .file-item .link:hover { text-decoration: underline; }
        .file-viewer {
            background: #0a0a0a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1.5rem; margin-bottom: 2rem; font-family: monospace; font-size: 0.9rem;
            white-space: pre-wrap; color: #10b981; min-height: 100px;
        }
        .info { background: #0a1a0a; border: 1px solid #003f00; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #10b981; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>文件查看器</h1>
        <p class="subtitle">查看系统文件 | CyberRange Other Easy Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个目录遍历靶场。文件查看功能没有正确过滤路径，可以访问任意文件。</p>
            <p>攻击者可以使用 <code>../</code> 跳出当前目录，访问系统敏感文件。</p>
            <p>试试: <code>?file=../flag.txt</code> 或 <code>?file=../../etc/passwd</code></p>
        </div>

        <div class="file-list">
            <h3>可用文件</h3>
            <div class="file-item">
                <span class="name">readme.txt</span>
                <a href="?file=readme.txt" class="link">查看</a>
            </div>
            <div class="file-item">
                <span class="name">help.txt</span>
                <a href="?file=help.txt" class="link">查看</a>
            </div>
            <div class="file-item">
                <span class="name">config.txt</span>
                <a href="?file=config.txt" class="link">查看</a>
            </div>
            <div class="file-item">
                <span class="name">flag.txt</span>
                <a href="?file=flag.txt" class="link">查看</a>
            </div>
        </div>

        <?php if ($file): ?>
        <div class="file-viewer">
            <?php
            // VULNERABLE: Directory traversal
            $filepath = "files/" . $file;
            if (file_exists($filepath)) {
                echo htmlspecialchars(file_get_contents($filepath));
            } else {
                // Try direct path (allows traversal)
                if (file_exists($file)) {
                    echo htmlspecialchars(file_get_contents($file));
                } else {
                    echo "文件不存在: " . htmlspecialchars($file);
                }
            }
            ?>
        </div>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>代码直接拼接用户输入到文件路径：</p>
            <p><code>$filepath = "files/" . $file;</code></p>
            <p>当文件不存在时，尝试直接读取用户输入的路径：</p>
            <p><code>if (file_exists($file)) echo file_get_contents($file);</code></p>
            <p>攻击者可以使用 <code>../</code> 跳出 files 目录：</p>
            <p><code>?file=../flag.txt</code></p>
            <p><code>?file=../../etc/passwd</code></p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>
</body>
</html>
