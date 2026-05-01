<?php
session_start();
$error = '';
$success = '';
$flag = '';

// Simple user database
$users = [
    'admin' => ['password' => 'admin123', 'role' => 'admin'],
    'user1' => ['password' => 'password1', 'role' => 'user'],
    'test' => ['password' => 'test', 'role' => 'user'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // VULNERABLE: SQL injection in login (simulated)
    // In a real scenario, this would be a database query
    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $users[$username]['role'];
        $success = "登录成功！欢迎, " . htmlspecialchars($username);
        if ($users[$username]['role'] === 'admin') {
            $flag = 'CR{brute_force_easy}';
        }
    } else {
        $error = '用户名或密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auth Easy - 暴力破解</title>
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
        .login-form h3 { margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #a1a1aa; font-size: 0.9rem; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input:focus { outline: none; border-color: #ef4444; }
        button {
            padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem; width: 100%;
        }
        button:hover { background: #dc2626; }
        .success {
            background: #001a00; border: 1px solid #003f00; border-radius: 8px;
            padding: 1rem; margin-bottom: 2rem; color: #10b981;
        }
        .error {
            background: #1a0000; border: 1px solid #3f0000; border-radius: 8px;
            padding: 1rem; margin-bottom: 2rem; color: #ef4444;
        }
        .flag {
            background: #1a1a00; border: 1px solid #3f3f00; border-radius: 8px;
            padding: 1rem; margin-bottom: 2rem; color: #f59e0b; font-family: monospace;
        }
        .info { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>登录页面</h1>
        <p class="subtitle">暴力破解入门 | CyberRange Auth Easy Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个暴力破解靶场。登录表单没有防暴力破解机制。</p>
            <p>攻击者可以使用字典或暴力工具尝试大量密码组合。</p>
            <p>常用工具：<code>Burp Suite Intruder</code>、<code>Hydra</code>、<code>Python 脚本</code></p>
        </div>

        <div class="login-form">
            <h3>用户登录</h3>
            <form method="POST">
                <div class="form-group">
                    <label>用户名</label>
                    <input type="text" name="username" placeholder="请输入用户名" required>
                </div>
                <div class="form-group">
                    <label>密码</label>
                    <input type="password" name="password" placeholder="请输入密码" required>
                </div>
                <button type="submit">登录</button>
            </form>
        </div>

        <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($flag): ?>
        <div class="flag">Flag: <?php echo $flag; ?></div>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>登录表单没有防暴力破解机制：</p>
            <p>1. 没有验证码（CAPTCHA）</p>
            <p>2. 没有账户锁定</p>
            <p>3. 没有请求频率限制</p>
            <p>4. 没有 CSRF Token</p>
            <p>攻击者可以使用 <code>Burp Suite</code> 或 <code>Hydra</code> 暴力破解密码</p>
            <p>flag 格式: <code>CR{...}</code>（管理员登录成功后显示）</p>
        </div>
    </div>
</body>
</html>
