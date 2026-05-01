<?php
// Set Content Security Policy header
// This lab teaches CSP bypass techniques
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:");

$search = $_GET['q'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSS Hell - CSP 绕过</title>
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
        .search-box { display: flex; gap: 1rem; margin-bottom: 2rem; }
        input[type="text"] {
            flex: 1; padding: 0.75rem 1rem; background: #1a1a1a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input[type="text"]:focus { outline: none; border-color: #ef4444; }
        button {
            padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #dc2626; }
        .result { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; }
        .result h3 { color: #a1a1aa; font-size: 0.9rem; margin-bottom: 1rem; }
        .query-display { background: #1e1e2e; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-family: monospace; font-size: 0.85rem; color: #a1a1aa; }
        .csp-info { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .csp-info h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .csp-info p { color: #a1a1aa; font-size: 0.85rem; margin-bottom: 0.5rem; }
        .csp-info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; word-break: break-all; }
        .info { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .warning { background: #2a1a00; border: 1px solid #5f3f00; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .warning h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .warning p { color: #a1a1aa; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>安全搜索系统 (CSP 保护)</h1>
        <p class="subtitle">有 CSP 保护的搜索功能 | CyberRange XSS Hell Lab</p>

        <div class="csp-info">
            <h3>Content Security Policy (CSP)</h3>
            <p>当前页面启用了 CSP 保护:</p>
            <p><code>default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:</code></p>
            <p>CSP 限制了资源加载来源，但这个配置有弱点可以利用。</p>
        </div>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个 CSP 绕过靶场。页面有 CSP 保护，直接注入 <code>&lt;script&gt;</code> 可能被拦截。</p>
            <p>你需要分析 CSP 策略，找到可以利用的弱点。</p>
            <p>提示：CSP 允许 <code>'unsafe-inline'</code>，这意味着什么？</p>
        </div>

        <div class="warning">
            <h3>难度提示</h3>
            <p>这是一个高级靶场。如果你还不熟悉 CSP，建议先完成其他 XSS 靶场。</p>
            <p>了解 CSP: <a href="https://developer.mozilla.org/zh-CN/docs/Web/HTTP/CSP" style="color: #f59e0b;">MDN CSP 文档</a></p>
        </div>

        <form method="GET" class="search-box">
            <input type="text" name="q" placeholder="搜索安全公告..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">搜索</button>
        </form>

        <?php if ($search !== ''): ?>
            <div class="query-display">
                搜索关键词: <?php echo $search; ?>
            </div>

            <div class="result">
                <h3>搜索结果</h3>
                <?php
                $announcements = [
                    ['title' => 'CVE-2024-1234', 'desc' => '远程代码执行漏洞，影响 Apache 2.4.x', 'severity' => '严重'],
                    ['title' => 'CVE-2024-5678', 'desc' => 'SQL 注入漏洞，影响 MySQL 8.0', 'severity' => '高危'],
                    ['title' => 'CVE-2024-9012', 'desc' => 'XSS 漏洞，影响 Chrome 浏览器', 'severity' => '中危'],
                    ['title' => 'CVE-2024-3456', 'desc' => '信息泄露漏洞，影响 Nginx 1.25', 'severity' => '低危'],
                ];

                $found = false;
                foreach ($announcements as $a) {
                    if (stripos($a['title'], $search) !== false || stripos($a['desc'], $search) !== false) {
                        $found = true;
                        echo '<div style="padding: 0.5rem 0; border-bottom: 1px solid #27272a;">';
                        echo '<h4 style="color: #f5f5f5;">' . htmlspecialchars($a['title']) . ' <span style="font-size: 0.8rem; color: #ef4444;">' . htmlspecialchars($a['severity']) . '</span></h4>';
                        echo '<p style="color: #a1a1aa; font-size: 0.85rem;">' . htmlspecialchars($a['desc']) . '</p>';
                        echo '</div>';
                    }
                }

                if (!$found) {
                    echo '<p style="color: #a1a1aa;">未找到与 "' . $search . '" 相关的安全公告</p>';
                    // This is where the XSS happens - search term is reflected
                    echo '<p style="color: #a1a1aa;">搜索词: ' . $search . '</p>';
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>虽然页面有 CSP 保护，但 <code>script-src 'self' 'unsafe-inline'</code> 允许内联脚本执行。</p>
            <p>这意味着 <code>&lt;script&gt;</code> 标签和事件处理器（如 <code>onerror</code>、<code>onload</code>）仍然有效。</p>
            <p>真正的 CSP 保护应该使用 nonce 或 hash，而不是 <code>'unsafe-inline'</code>。</p>
            <p>试试: <code>&lt;script&gt;alert(document.domain)&lt;/script&gt;</code></p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>

    <script>
        // Log CSP violations for educational purposes
        document.addEventListener('securitypolicyviolation', function(e) {
            console.log('CSP Violation:', {
                directive: e.violatedDirective,
                blocked: e.blockedURI,
                original: e.originalPolicy
            });
        });
    </script>
</body>
</html>
