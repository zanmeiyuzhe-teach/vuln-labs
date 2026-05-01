<?php
require_once 'config.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$result = null;
$error = '';
$query = '';
$blocked = false;

// Simple WAF (Web Application Firewall) simulation
function checkWAF($input) {
    $blocked_patterns = [
        '/\bunion\b/i',
        '/\bselect\b/i',
        '/\binsert\b/i',
        '/\bupdate\b/i',
        '/\bdelete\b/i',
        '/\bdrop\b/i',
        '/\bor\b.*=/i',
        '/\band\b.*=/i',
        '/--/',
        '/#/',
        '/\*/',
        '/\bxp_/i',
        '/\bexec\b/i',
        '/\bexecute\b/i',
        '/\bchar\b/i',
        '/\bconcat\b/i',
        '/\bgroup_concat\b/i',
        '/\binformation_schema\b/i',
    ];

    foreach ($blocked_patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $username !== '' && $password !== '') {
    // Check WAF
    if (checkWAF($username) || checkWAF($password)) {
        $blocked = true;
        $error = 'WAF: 检测到可疑输入，请求已被拦截！';
    } else {
        // VULNERABLE: Login bypass with WAF
        $query = "SELECT id, username, role FROM users WHERE username = '$username' AND password = '$password'";
        $result = mysqli_query($conn, $query);

        if ($result === false) {
            $error = mysqli_error($conn);
        } elseif (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $result = "登录成功！欢迎，" . htmlspecialchars($row['username']) . " (角色: " . htmlspecialchars($row['role']) . ")";
        } else {
            $result = '用户名或密码错误';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Hell - WAF 绕过</title>
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
        .login-form {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .login-form h3 { color: #f5f5f5; margin-bottom: 1.5rem; font-size: 1.1rem; }
        .form-row { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; }
        .form-row label { color: #a1a1aa; font-size: 0.9rem; }
        input[type="text"], input[type="password"] {
            padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input[type="text"]:focus, input[type="password"]:focus { outline: none; border-color: #ef4444; }
        button {
            padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem; width: 100%;
        }
        button:hover { background: #dc2626; }
        .result { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .result.success { background: #0a1a0a; border: 1px solid #003f00; color: #10b981; }
        .result.error { background: #1a0000; border: 1px solid #3f0000; color: #ef4444; }
        .result.blocked { background: #1a1a00; border: 1px solid #3f3f00; color: #f59e0b; }
        .query-display { background: #1e1e2e; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-family: monospace; font-size: 0.85rem; color: #a1a1aa; }
        .waf-info { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .waf-info h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .waf-info p { color: #a1a1aa; font-size: 0.85rem; margin-bottom: 0.5rem; }
        .info { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .bypass-techniques { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .bypass-techniques h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .bypass-techniques p { color: #a1a1aa; font-size: 0.85rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>安全登录系统 (WAF 保护)</h1>
        <p class="subtitle">有 WAF 保护的登录功能 | CyberRange SQL Injection Hell Lab</p>

        <div class="waf-info">
            <h3>Web Application Firewall (WAF)</h3>
            <p>当前页面启用了 WAF 保护，会拦截包含以下关键字的输入:</p>
            <p>union, select, insert, update, delete, drop, or=, and=, --, #, *, char, concat, information_schema 等</p>
            <p>直接使用这些关键字会被拦截，你需要找到绕过方法。</p>
        </div>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个 WAF 绕过靶场。登录功能有 SQL 注入漏洞，但被 WAF 保护。</p>
            <p>你需要使用各种编码和变形技术绕过 WAF 检测。</p>
            <p>这是一个高级挑战，需要深入理解 SQL 语法和 WAF 工作原理。</p>
        </div>

        <div class="login-form">
            <h3>用户登录</h3>
            <form method="POST">
                <div class="form-row">
                    <label>用户名</label>
                    <input type="text" name="username" placeholder="输入用户名" value="<?php echo htmlspecialchars($username); ?>">
                </div>
                <div class="form-row">
                    <label>密码</label>
                    <input type="text" name="password" placeholder="输入密码" value="<?php echo htmlspecialchars($password); ?>">
                </div>
                <button type="submit">登录</button>
            </form>
        </div>

        <?php if ($query): ?>
            <div class="query-display">
                执行的 SQL: <?php echo htmlspecialchars($query); ?>
            </div>
        <?php endif; ?>

        <?php if ($blocked): ?>
            <div class="result blocked">
                <strong>WAF 拦截:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($error): ?>
            <div class="result error">
                <strong>错误:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($result): ?>
            <div class="result <?php echo strpos($result, '成功') !== false ? 'success' : 'error'; ?>">
                <?php echo $result; ?>
            </div>
        <?php endif; ?>

        <div class="bypass-techniques">
            <h3>WAF 绕过技术</h3>
            <p><strong>大小写混合:</strong> <code>UnIoN SeLeCt</code> 可以绕过简单的关键字匹配</p>
            <p><strong>双写绕过:</strong> <code>ununionion selselectect</code> 过滤后变成 <code>union select</code></p>
            <p><strong>编码绕过:</strong> URL 编码、Unicode 编码、十六进制编码</p>
            <p><strong>内联注释:</strong> <code>/*!union*/ /*!select*/</code> MySQL 特有语法</p>
            <p><strong>换行符:</strong> 在关键字中插入换行符 <code>uni%0aon</code></p>
        </div>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>虽然有 WAF 保护，但 WAF 的规则不够完善，存在绕过方法。</p>
            <p>真正的安全防护应该使用参数化查询（Prepared Statements），而不是依赖 WAF。</p>
            <p>WAF 只是增加攻击难度，不能从根本上解决 SQL 注入问题。</p>
            <p>flag 格式: <code>CR{...}</code> (成功登录 admin 后显示)</p>
        </div>
    </div>
</body>
</html>
