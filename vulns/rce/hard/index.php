<?php
                                                                                                                                                                                                                                                                                                                                                                                                         
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCE Hard - eval 代码执行</title>
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
        .calc-form {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .calc-form h3 { margin-bottom: 1.5rem; }
        .form-row { display: flex; gap: 1rem; }
        input[type="text"] {
            flex: 1; padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input:focus { outline: none; border-color: #f59e0b; }
        button {
            padding: 0.75rem 1.5rem; background: #f59e0b; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #d97706; }
        .output {
            background: #0a0a0a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1.5rem; margin-bottom: 2rem; font-family: monospace; font-size: 0.9rem;
            white-space: pre-wrap; color: #f59e0b; min-height: 100px;
        }
        .info { background: #1a0a00; border: 1px solid #3f2000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .examples {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem;
        }
        .examples h3 { color: #a1a1aa; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .examples p { color: #a1a1aa; font-size: 0.85rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP 代码执行器</h1>
        <p class="subtitle">在线 PHP 代码测试 | CyberRange RCE Hard Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场使用 PHP 的 <code>eval()</code> 函数执行用户输入的代码。</p>
            <p><code>eval()</code> 会将字符串作为 PHP 代码执行，这是非常危险的函数。</p>
            <p>攻击者可以执行任意 PHP 代码，包括系统命令执行。</p>
        </div>

        <div class="examples">
            <h3>示例代码</h3>
            <p><code>echo "Hello World";</code> - 输出字符串</p>
            <p><code>echo 1 + 2;</code> - 数学运算</p>
            <p><code>phpinfo();</code> - 显示 PHP 信息</p>
            <p><code>system('whoami');</code> - 执行系统命令</p>
        </div>

        <div class="calc-form">
            <h3>输入 PHP 代码</h3>
            <form method="POST">
                <div class="form-row">
                    <input type="text" name="expr" placeholder="输入 PHP 代码..." value="<?php echo htmlspecialchars($expression); ?>">
                    <button type="submit">执行</button>
                </div>
            </form>
        </div>

        <?php if ($output): ?>
            <div class="output"><?php echo htmlspecialchars($output); ?></div>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>代码直接将用户输入传递给 <code>eval()</code> 函数：</p>
            <p><code>eval($expression);</code></p>
            <p>这是 PHP 中最危险的函数之一，它会执行任何有效的 PHP 代码。</p>
            <p>攻击者可以使用以下函数执行系统命令：</p>
            <p><code>system()</code>, <code>exec()</code>, <code>shell_exec()</code>, <code>passthru()</code>, <code>popen()</code></p>
            <p>或者使用反引号：<code>`whoami`</code></p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>
</body>
</html>
