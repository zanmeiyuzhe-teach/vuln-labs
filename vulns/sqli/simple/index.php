<?php
require_once 'config.php';

$search = $_GET['search'] ?? '';
$results = null;
$error = '';
$query = '';

if ($search !== '') {
    // VULNERABLE: String-based SQL injection
    // User input is wrapped in quotes but not properly escaped
    $query = "SELECT id, username, email, role FROM users WHERE username LIKE '%$search%'";
    $result = mysqli_query($conn, $query);

    if ($result === false) {
        $error = mysqli_error($conn);
    } else {
        $results = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Simple - 用户搜索</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #3b82f6; margin-bottom: 0.5rem; }
        .subtitle { color: #a1a1aa; margin-bottom: 2rem; font-size: 0.9rem; }
        .search-box { display: flex; gap: 1rem; margin-bottom: 2rem; }
        input[type="text"] {
            flex: 1; padding: 0.75rem 1rem; background: #1a1a1a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input[type="text"]:focus { outline: none; border-color: #3b82f6; }
        button {
            padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #2563eb; }
        .hint { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .hint h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .hint p { color: #a1a1aa; font-size: 0.85rem; }
        .hint code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .result-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .result-table th, .result-table td {
            padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #27272a;
        }
        .result-table th { color: #a1a1aa; font-size: 0.85rem; text-transform: uppercase; }
        .result-table td { font-size: 0.95rem; }
        .error { color: #ef4444; background: #1a0000; padding: 1rem; border-radius: 8px; border: 1px solid #3f0000; }
        .query-display { background: #1e1e2e; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-family: monospace; font-size: 0.85rem; color: #a1a1aa; }
        .info { background: #0a1a0a; border: 1px solid #003f00; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #10b981; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>用户搜索系统</h1>
        <p class="subtitle">搜索用户信息 | CyberRange SQL Injection Simple Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个字符型 SQL 注入靶场。用户输入被包裹在单引号中，但没有正确转义。</p>
            <p>与数字型注入不同，你需要先闭合单引号才能注入 SQL 代码。</p>
            <p>试试: <code>' OR '1'='1</code> 或 <code>' UNION SELECT 1,2,3,4 -- -</code></p>
        </div>

        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="搜索用户名..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">搜索</button>
        </form>

        <?php if ($query): ?>
            <div class="query-display">
                执行的 SQL: <?php echo htmlspecialchars($query); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error">
                <strong>SQL 错误:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($results !== null && count($results) > 0): ?>
            <table class="result-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户名</th>
                        <th>邮箱</th>
                        <th>角色</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['username'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['role'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($results !== null && count($results) === 0): ?>
            <p style="color: #a1a1aa;">未找到用户</p>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>用户输入被直接拼接到 LIKE 子句中：<code>WHERE username LIKE '%$search%'</code></p>
            <p>你需要先闭合前面的单引号，然后注入 UNION SELECT 查询其他表的数据。</p>
            <p>注意：注释符在 MySQL 中可以用 <code>-- -</code> 或 <code>#</code> 或 <code>%23</code></p>
            <p>flag 格式: <code>CR{...}</code> (在 flags 表中)</p>
        </div>
    </div>
</body>
</html>
