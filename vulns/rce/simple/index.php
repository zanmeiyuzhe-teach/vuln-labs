<?php
$output = '';
$ip = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ip'])) {
    $ip = $_POST['ip'];

    // Simple filter: block some common injection characters
    // VULNERABLE: Filter is incomplete and can be bypassed
    $blacklist = [';', '&&', '||'];

    $blocked = false;
    foreach ($blacklist as $char) {
        if (strpos($ip, $char) !== false) {
            $blocked = true;
            break;
        }
    }

    if ($blocked) {
        $output = '错误：检测到非法字符！';
    } else {
        // Still vulnerable to pipe | and backticks
        $output = shell_exec("ping -c 4 " . $ip);
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCE Simple - 命令拼接绕过</title>
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
        .ping-form {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .ping-form h3 { margin-bottom: 1.5rem; }
        .form-row { display: flex; gap: 1rem; }
        input[type="text"] {
            flex: 1; padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input:focus { outline: none; border-color: #3b82f6; }
        button {
            padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #2563eb; }
        .output {
            background: #0a0a0a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1.5rem; margin-bottom: 2rem; font-family: monospace; font-size: 0.9rem;
            white-space: pre-wrap; color: #3b82f6; min-height: 100px;
        }
        .output.error { color: #ef4444; }
        .filter-info {
            background: #1a1a00; border: 1px solid #3f3f00; border-radius: 8px; padding: 1rem; margin-bottom: 2rem;
        }
        .filter-info h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .filter-info p { color: #a1a1aa; font-size: 0.85rem; }
        .info { background: #0a0a1a; border: 1px solid #00003f; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #3b82f6; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>网络诊断工具</h1>
        <p class="subtitle">Ping 测试 (有过滤) | CyberRange RCE Simple Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场有简单的输入过滤，阻止了部分命令注入字符。</p>
            <p>但过滤不完整，攻击者可以使用其他方式绕过过滤。</p>
            <p>试试: <code>127.0.0.1 | whoami</code> 或 <code>127.0.0.1 `id`</code></p>
        </div>

        <div class="filter-info">
            <h3>过滤规则</h3>
            <p>以下字符被过滤: <code>;</code> <code>&&</code> <code>||</code></p>
            <p>但管道符 <code>|</code> 和反引号 <code>`</code> 没有过滤！</p>
        </div>

        <div class="ping-form">
            <h3>Ping 测试</h3>
            <form method="POST">
                <div class="form-row">
                    <input type="text" name="ip" placeholder="输入 IP 地址或域名" value="<?php echo htmlspecialchars($ip); ?>">
                    <button type="submit">Ping</button>
                </div>
            </form>
        </div>

        <?php if ($output): ?>
            <div class="output <?php echo strpos($output, '错误') !== false ? 'error' : ''; ?>"><?php echo htmlspecialchars($output); ?></div>
        <?php endif; ?>

        <div class="info">
            <h3>绕过技术</h3>
            <p><strong>管道符绕过:</strong> <code>127.0.0.1 | whoami</code></p>
            <p><strong>反引号绕过:</strong> <code>127.0.0.1 `whoami`</code></p>
            <p><strong>$() 替换:</strong> <code>127.0.0.1 $(whoami)</code></p>
            <p><strong>换行符绕过:</strong> <code>127.0.0.1%0awhoami</code></p>
            <p><strong>空格绕过:</strong> <code>127.0.0.1|{whoami,}</code></p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>
</body>
</html>
