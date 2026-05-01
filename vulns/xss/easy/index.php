<?php
$search = $_GET['q'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSS Easy - 搜索功能</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #f59e0b; margin-bottom: 0.5rem; }
        .subtitle { color: #a1a1aa; margin-bottom: 2rem; font-size: 0.9rem; }
        .search-box { display: flex; gap: 1rem; margin-bottom: 2rem; }
        input[type="text"] {
            flex: 1; padding: 0.75rem 1rem; background: #1a1a1a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input[type="text"]:focus { outline: none; border-color: #f59e0b; }
        button {
            padding: 0.75rem 1.5rem; background: #f59e0b; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #d97706; }
        .hint { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .hint h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .hint p { color: #a1a1aa; font-size: 0.85rem; }
        .hint code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .result { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; }
        .result h3 { color: #a1a1aa; font-size: 0.9rem; margin-bottom: 1rem; }
        .search-result { padding: 0.5rem 0; border-bottom: 1px solid #27272a; }
        .search-result:last-child { border-bottom: none; }
        .search-result h4 { color: #f5f5f5; margin-bottom: 0.25rem; }
        .search-result p { color: #a1a1aa; font-size: 0.85rem; }
        .search-result .price { color: #10b981; font-weight: 600; }
        .query-display { background: #1e1e2e; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-family: monospace; font-size: 0.85rem; color: #a1a1aa; }
        .info { background: #1a0a00; border: 1px solid #3f2000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>产品搜索系统</h1>
        <p class="subtitle">搜索你感兴趣的产品 | CyberRange XSS Easy Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个反射型 XSS 靶场。搜索关键词直接输出到页面，没有任何过滤或转义。</p>
            <p>试试: <code>&lt;script&gt;alert(1)&lt;/script&gt;</code></p>
            <p>或者: <code>&lt;img src=x onerror=alert(1)&gt;</code></p>
        </div>

        <form method="GET" class="search-box">
            <input type="text" name="q" placeholder="搜索产品..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">搜索</button>
        </form>

        <?php if ($search !== ''): ?>
            <div class="query-display">
                搜索关键词: <?php echo $search; ?>
            </div>

            <div class="result">
                <h3>搜索结果</h3>
                <?php
                // Fake search results
                $products = [
                    ['name' => '无线鼠标', 'desc' => '人体工学设计，2.4G 无线连接', 'price' => '¥99'],
                    ['name' => '机械键盘', 'desc' => 'RGB 背光，Cherry MX 轴', 'price' => '¥399'],
                    ['name' => 'USB-C 扩展坞', 'desc' => '7合1，支持 4K HDMI', 'price' => '¥199'],
                    ['name' => '网络摄像头', 'desc' => '1080p 高清，内置麦克风', 'price' => '¥159'],
                ];

                $found = false;
                foreach ($products as $p) {
                    if (stripos($p['name'], $search) !== false || stripos($p['desc'], $search) !== false) {
                        $found = true;
                        echo '<div class="search-result">';
                        echo '<h4>' . htmlspecialchars($p['name']) . '</h4>';
                        echo '<p>' . htmlspecialchars($p['desc']) . '</p>';
                        echo '<span class="price">' . $p['price'] . '</span>';
                        echo '</div>';
                    }
                }

                if (!$found) {
                    echo '<p style="color: #a1a1aa;">未找到与 "' . $search . '" 相关的产品</p>';
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>注意搜索关键词在页面中的输出位置。用户输入被直接嵌入到 HTML 中，没有经过转义处理。</p>
            <p>攻击者可以注入任意 HTML 和 JavaScript 代码，窃取用户的 Cookie 或执行恶意操作。</p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>
</body>
</html>
