<?php
session_start();
$error = '';
$success = '';
$flag = '';
$captcha_error = '';

// Generate CAPTCHA
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = rand(1000, 9999);
}

$users = [
    'admin' => ['password' => 'password123', 'role' => 'admin'],
    'user1' => ['password' => 'qwerty', 'role' => 'user'],
    'test' => ['password' => '123456', 'role' => 'user'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';

    // VULNERABLE: CAPTCHA not refreshed after failed attempt
    // Same CAPTCHA can be reused
    if ($captcha !== (string)$_SESSION['captcha']) {
        $captcha_error = '验证码错误';
    } else {
        if (isset($users[$username]) && $users[$username]['password'] === $password) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $users[$username]['role'];
            $success = "登录成功！欢迎, " . htmlspecialchars($username);
            if ($users[$username]['role'] === 'admin') {
                $flag = 'CR{captcha_bypass}';
            }
        } else {
            $error = '用户名或密码错误';
            // VULNERABLE: CAPTCHA not regenerated on failure
            // Attacker can reuse the same CAPTCHA for all attempts
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auth Simple - 验证码绕过</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #3b82f6; margin-bottom: 0.5rem; }
        .subtitle { color: #a1a1aa; margin-bottom: 2rem; font-size: 0.9rem; }
        .hint { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .hint h3 { color: #3b82f6; font-size: 0.9rem; margin-bottom: 0.5rem; }
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
        input:focus { outline: none; border-color: #3b82f6; }
        .captcha-row { display: flex; gap: 1rem; align-items: center; }
        .captcha-row input { flex: 1; }
        .captcha-code {
            background: #27272a; padding: 0.75rem 1rem; border-radius: 8px;
            font-family: monospace; font-size: 1.2rem; letter-spacing: 0.3rem;
            color: #f59e0b; user-select: none;
        }
        button {
            padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem; width: 100%;
        }
        button:hover { background: #2563eb; }
        .success { background: #001a00; border: 1px solid #003f00; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; color: #10b981; }
        .error { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; color: #ef4444; }
        .flag { background: #1a1a00; border: 1px solid #3f3f00; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; color: #f59e0b; font-family: monospace; }
        .info { background: #0a0a1a; border: 1px solid #00003f; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #3b82f6; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>登录页面（有验证码）</h1>
        <p class="subtitle">验证码绕过 | CyberRange Auth Simple Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场有验证码保护，但验证码实现存在缺陷。</p>
            <p>验证码在失败后不会刷新，可以重复使用同一个验证码进行暴力破解。</p>
            <p>提示：<code>先获取一次验证码，然后在 Burp Suite 中固定验证码值进行爆破</code></p>
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
                <div class="form-group">
                    <label>验证码</label>
                    <div class="captcha-row">
                        <input type="text" name="captcha" placeholder="请输入验证码" required>
                        <div class="captcha-code"><?php echo $_SESSION['captcha']; ?></div>
                    </div>
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

        <?php if ($captcha_error): ?>
        <div class="error"><?php echo $captcha_error; ?></div>
        <?php endif; ?>

        <?php if ($flag): ?>
        <div class="flag">Flag: <?php echo $flag; ?></div>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>验证码实现存在缺陷：</p>
            <p>1. 验证码在登录失败后不会重新生成</p>
            <p>2. 同一个验证码可以多次使用</p>
            <p>3. 验证码显示在页面上（实际应用中可能是图片）</p>
            <p>攻击步骤：</p>
            <p>1. 获取一次验证码（如 <code>1234</code>）</p>
            <p>2. 在 Burp Suite 中固定 <code>captcha=1234</code></p>
            <p>3. 只爆破 <code>username</code> 和 <code>password</code></p>
            <p>flag 格式: <code>CR{...}</code>（管理员登录成功后显示）</p>
        </div>
    </div>
</body>
</html>
