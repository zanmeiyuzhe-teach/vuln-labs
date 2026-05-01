<?php
$output = '';
$input = '';
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST['input'] ?? '';

    switch ($action) {
        case 'ping':
            // VULNERABLE: Command injection in ping
            $output = shell_exec("ping -c 2 " . $input);
            break;

        case 'calc':
            // VULNERABLE: eval() with weak filtering
            $filtered = str_replace(['<?php', '?>', '<?'], '', $input);
            ob_start();
            try {
                eval($filtered);
            } catch (Throwable $e) {
                echo "Error: " . $e->getMessage();
            }
            $output = ob_get_clean();
            break;

        case 'file':
            // VULNERABLE: File read with path traversal
            $file = '/var/www/html/' . $input;
            if (file_exists($file)) {
                $output = file_get_contents($file);
            } else {
                $output = "File not found: " . $input;
            }
            break;

        case 'template':
            // VULNERABLE: Server-Side Template Injection (SSTI-like)
            $template = "Hello, " . $input . "! Welcome to the system.";
            $output = $template;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCE Hell - 综合命令执行</title>
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
        .tools-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem; }
        .tool-card {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1.5rem; cursor: pointer; transition: all 0.2s;
        }
        .tool-card:hover { border-color: #ef4444; }
        .tool-card.active { border-color: #ef4444; background: #1a0000; }
        .tool-card h4 { margin-bottom: 0.5rem; }
        .tool-card p { color: #a1a1aa; font-size: 0.85rem; }
        .tool-form {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .tool-form h3 { margin-bottom: 1.5rem; }
        .form-row { display: flex; gap: 1rem; }
        input[type="text"] {
            flex: 1; padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input:focus { outline: none; border-color: #ef4444; }
        button {
            padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #dc2626; }
        .output {
            background: #0a0a0a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1.5rem; margin-bottom: 2rem; font-family: monospace; font-size: 0.9rem;
            white-space: pre-wrap; color: #ef4444; min-height: 100px;
        }
        .info { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>系统管理工具箱</h1>
        <p class="subtitle">多功能系统管理 | CyberRange RCE Hell Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场包含多个有漏洞的工具，每个都有不同的命令执行方式。</p>
            <p>你需要找到所有漏洞点，并利用它们获取 flag。</p>
            <p>这是一个综合性的高级挑战，需要掌握多种 RCE 技术。</p>
        </div>

        <div class="tools-grid">
            <div class="tool-card <?php echo $action === 'ping' ? 'active' : ''; ?>" onclick="selectTool('ping')">
                <h4>Ping 工具</h4>
                <p>网络连通性测试</p>
            </div>
            <div class="tool-card <?php echo $action === 'calc' ? 'active' : ''; ?>" onclick="selectTool('calc')">
                <h4>计算器</h4>
                <p>PHP 代码执行器</p>
            </div>
            <div class="tool-card <?php echo $action === 'file' ? 'active' : ''; ?>" onclick="selectTool('file')">
                <h4>文件查看器</h4>
                <p>查看系统文件</p>
            </div>
            <div class="tool-card <?php echo $action === 'template' ? 'active' : ''; ?>" onclick="selectTool('template')">
                <h4>模板引擎</h4>
                <p>生成欢迎信息</p>
            </div>
        </div>

        <div class="tool-form">
            <h3 id="tool-title"><?php
                $titles = ['ping' => 'Ping 测试', 'calc' => 'PHP 计算器', 'file' => '文件查看器', 'template' => '模板引擎'];
                echo $titles[$action] ?? '选择工具';
            ?></h3>
            <form method="POST" id="toolForm">
                <input type="hidden" name="action" id="actionInput" value="<?php echo $action; ?>">
                <div class="form-row">
                    <input type="text" name="input" id="toolInput" placeholder="<?php
                        $placeholders = ['ping' => '输入 IP 地址', 'calc' => '输入 PHP 表达式', 'file' => '输入文件名', 'template' => '输入你的名字'];
                        echo $placeholders[$action] ?? '输入内容';
                    ?>" value="<?php echo htmlspecialchars($input); ?>">
                    <button type="submit">执行</button>
                </div>
            </form>
        </div>

        <?php if ($output): ?>
            <div class="output"><?php echo htmlspecialchars($output); ?></div>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞清单</h3>
            <p><strong>Ping 工具:</strong> 命令注入 - <code>127.0.0.1; cat /flag</code></p>
            <p><strong>计算器:</strong> eval() 执行 - <code>system('cat /flag');</code></p>
            <p><strong>文件查看器:</strong> 路径遍历 - <code>../../../flag</code></p>
            <p><strong>模板引擎:</strong> 代码注入 - <code><?php echo '<?php system("cat /flag"); ?>'; ?></code></p>
            <p>flag 格式: <code>CR{...}</code> (在 /flag 文件中)</p>
        </div>
    </div>

    <script>
        function selectTool(tool) {
            document.getElementById('actionInput').value = tool;
            document.getElementById('toolForm').submit();
        }
    </script>
</body>
</html>
