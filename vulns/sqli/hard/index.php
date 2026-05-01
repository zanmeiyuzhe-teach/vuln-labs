<?php
require_once 'config.php';

$id = $_GET['id'] ?? '';
$exists = false;
$error = '';
$query = '';

if ($id !== '') {
    // VULNERABLE: Boolean-based blind SQL injection
    // No error messages shown, only "exists" or "not exists"
    $query = "SELECT id FROM products WHERE id = '$id'";
    $result = mysqli_query($conn, $query);

    if ($result !== false) {
        $exists = mysqli_num_rows($result) > 0;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Hard - 盲注</title>
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
        .result-box {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem; text-align: center; font-size: 1.2rem;
        }
        .result-box.exists { border-color: #10b981; color: #10b981; }
        .result-box.not-exists { border-color: #ef4444; color: #ef4444; }
        .info { background: #1a0a00; border: 1px solid #3f2000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .techniques { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .techniques h3 { color: #a1a1aa; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .techniques p { color: #a1a1aa; font-size: 0.85rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>产品验证系统</h1>
        <p class="subtitle">验证产品 ID 是否存在 | CyberRange SQL Injection Hard Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个布尔盲注靶场。页面不显示查询结果，也不显示错误信息，只告诉你"存在"或"不存在"。</p>
            <p>你需要通过构造条件判断语句，逐字符推断出数据库中的数据。</p>
            <p>这是 SQL 注入的高级技术，需要耐心和技巧。</p>
        </div>

        <form method="GET" class="search-box">
            <input type="text" name="id" placeholder="输入产品 ID (1-8)" value="<?php echo htmlspecialchars($id); ?>">
            <button type="submit">验证</button>
        </form>

        <?php if ($id !== ''): ?>
            <div class="result-box <?php echo $exists ? 'exists' : 'not-exists'; ?>">
                <?php echo $exists ? '✓ 产品存在' : '✗ 产品不存在'; ?>
            </div>
        <?php endif; ?>

        <div class="techniques">
            <h3>盲注技术</h3>
            <p><strong>布尔盲注:</strong> 通过 AND 条件判断，如果条件为真则返回"存在"</p>
            <p><strong>时间盲注:</strong> 使用 <code>IF(condition, SLEEP(5), 0)</code> 通过响应时间判断</p>
            <p><strong>报错盲注:</strong> 使用 <code>EXTRACTVALUE</code> 或 <code>UPDATEXML</code> 触发报错</p>
        </div>

        <div class="hint">
            <h3>示例 Payload</h3>
            <p>判断数据库名第一个字符:</p>
            <p><code>1' AND ASCII(SUBSTRING(DATABASE(),1,1)) > 100 -- -</code></p>
            <p>判断 users 表是否存在:</p>
            <p><code>1' AND (SELECT COUNT(*) FROM users) > 0 -- -</code></p>
            <p>判断 admin 用户是否存在:</p>
            <p><code>1' AND (SELECT COUNT(*) FROM users WHERE username='admin') > 0 -- -</code></p>
        </div>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>这个页面使用字符型 SQL 查询，但不显示任何错误信息或查询结果。</p>
            <p>攻击者只能通过页面返回"存在"或"不存在"来推断数据。</p>
            <p>这需要构造大量的条件判断语句，逐字符逐字符地推断数据。</p>
            <p>推荐使用 SQLMap 等自动化工具进行盲注。</p>
            <p>flag 格式: <code>CR{...}</code> (在 flags 表中)</p>
        </div>
    </div>
</body>
</html>
