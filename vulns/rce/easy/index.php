<?php
$output = '';
$ip = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ip'])) {
    $ip = $_POST['ip'];
    // VULNERABLE: Direct command execution without sanitization
    $output = shell_exec("ping -c 4 " . $ip);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCE Easy - Ping 工具</title>
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
        input:focus { outline: none; border-color: #10b981; }
        button {
            padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #059669; }
        .output {
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
        <h1>网络诊断工具</h1>
        <p class="subtitle">Ping 测试 | CyberRange RCE Easy Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个远程命令执行靶场。Ping 功能直接将用户输入传递给系统命令执行。</p>
            <p>没有任何输入验证或过滤，攻击者可以注入任意系统命令。</p>
            <p>试试: <code>127.0.0.1; whoami</code> 或 <code>127.0.0.1 | cat /etc/passwd</code></p>
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
            <div class="output"><?php echo htmlspecialchars($output); ?></div>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>代码直接将用户输入拼接到系统命令中：</p>
            <p><code>$output = shell_exec("ping -c 4 " . $ip);</code></p>
            <p>攻击者可以使用以下方式注入命令：</p>
            <p><code>;</code> 分号 - 执行完前一个命令后执行后一个</p>
            <p><code>|</code> 管道 - 将前一个命令的输出作为后一个命令的输入</p>
            <p><code>&&</code> 逻辑与 - 前一个命令成功后执行后一个</p>
            <p><code>||</code> 逻辑或 - 前一个命令失败后执行后一个</p>
            <p><code>`command`</code> 反引号 - 命令替换</p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>
</body>
</html>
