<?php
$url = $_GET['url'] ?? '';
$result = '';
$error = '';

if ($url) {
    // VULNERABLE: SSRF - Server-Side Request Forgery
    // The server fetches URLs without validation
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response !== false) {
        $result = $response;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Hell - SSRF</title>
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
        .fetch-form {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .fetch-form h3 { margin-bottom: 1rem; }
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
        .result {
            background: #0a0a0a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1.5rem; margin-bottom: 2rem; font-family: monospace; font-size: 0.9rem;
            white-space: pre-wrap; color: #ef4444; max-height: 500px; overflow-y: auto;
        }
        .error {
            background: #1a0000; border: 1px solid #3f0000; border-radius: 8px;
            padding: 1rem; margin-bottom: 2rem; color: #ef4444;
        }
        .info { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .techniques {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem;
        }
        .techniques h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .techniques p { color: #a1a1aa; font-size: 0.85rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>URL 抓取工具</h1>
        <p class="subtitle">获取网页内容 | CyberRange Other Hell Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个 SSRF (Server-Side Request Forgery) 靶场。</p>
            <p>服务器会根据用户提供的 URL 发起请求，但没有验证 URL 的目标。</p>
            <p>攻击者可以利用这个功能访问内网服务、读取本地文件等。</p>
        </div>

        <div class="techniques">
            <h3>SSRF 攻击技术</h3>
            <p><strong>访问内网:</strong> <code>http://127.0.0.1:8080</code> 或 <code>http://localhost</code></p>
            <p><strong>读取文件:</strong> <code>file:///etc/passwd</code></p>
            <p><strong>端口扫描:</strong> <code>http://127.0.0.1:3306</code> (MySQL)</p>
            <p><strong>协议利用:</strong> <code>gopher://</code>, <code>dict://</code></p>
            <p><strong>内网服务:</strong> <code>http://192.168.1.1/admin</code></p>
        </div>

        <div class="fetch-form">
            <h3>输入 URL</h3>
            <form method="GET">
                <div class="form-row">
                    <input type="text" name="url" placeholder="输入要抓取的 URL" value="<?php echo htmlspecialchars($url); ?>">
                    <button type="submit">抓取</button>
                </div>
            </form>
        </div>

        <?php if ($error): ?>
        <div class="error">
            <strong>错误:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <?php if ($result): ?>
        <div class="result"><?php echo htmlspecialchars($result); ?></div>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>代码直接使用用户输入的 URL 发起请求：</p>
            <p><code>curl_setopt($ch, CURLOPT_URL, $url);</code></p>
            <p>没有验证 URL 的目标地址，攻击者可以：</p>
            <p>1. 访问内网服务：<code>?url=http://127.0.0.1:8080/admin</code></p>
            <p>2. 读取本地文件：<code>?url=file:///etc/passwd</code></p>
            <p>3. 扫描内网端口：<code>?url=http://192.168.1.1:3306</code></p>
            <p>4. 利用 Gopher 协议攻击内部服务</p>
            <p>flag 格式: <code>CR{...}</code> (在内网服务或本地文件中)</p>
        </div>
    </div>
</body>
</html>
