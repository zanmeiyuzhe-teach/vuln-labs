<?php
session_start();
$error = '';
$success = '';
$flag = '';
$blocked = '';

// Rate limiting (simulated - in real scenario would use Redis)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = 0;
}

// Generate CAPTCHA
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = rand(1000, 9999);
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$users = [
    'admin' => ['password' => 'Sup3rS3cur3P@ss!', 'role' => 'admin'],
    'user1' => ['password' => 'complexP@ss1', 'role' => 'user'],
    'test' => ['password' => 'T3st!ng123', 'role' => 'user'],
];

// Check rate limit
$time_since_last = time() - $_SESSION['last_attempt'];
if ($_SESSION['login_attempts'] >= 5 && $time_since_last < 60) {
    $blocked = "账户已锁定，请 " . (60 - $time_since_last) . " 秒后重试";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($blocked)) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['captcha'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token
    if (empty($token) || $token !== $_SESSION['csrf_token']) {
        $error = 'CSRF Token 无效';
    }
    // Validate CAPTCHA
    elseif ($captcha !== (string)$_SESSION['captcha']) {
        $error = '验证码错误';
        $_SESSION['captcha'] = rand(1000, 9999); // Regenerate
    }
    // Process login
    else {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();

        if (isset($users[$username]) && $users[$username]['password'] === $password) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $users[$username]['role'];
            $_SESSION['login_attempts'] = 0; // Reset on success
            $success = "登录成功！欢迎, " . htmlspecialchars($username);
            if ($users[$username]['role'] === 'admin') {
                $flag = 'CR{auth_hell_master}';
            }
        } else {
            $error = '用户名或密码错误';
            $_SESSION['captcha'] = rand(1000, 9999); // Regenerate on failure
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auth Hell - 综合防御</title>
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
        .captcha-row { display: flex; gap: 1rem; align-items: center; }
        .captcha-row input { flex: 1; }
        .captcha-code {
            background: #27272a; padding: 0.75rem 1rem; border-radius: 8px;
            font-family: monospace; font-size: 1.2rem; letter-spacing: 0.3rem;
            color: #f59e0b; user-select: none;
        }
        button {
            padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem; width: 100%;
        }
        button:hover { background: #dc2626; }
        button:disabled { background: #666; cursor: not-allowed; }
        .success { background: #001a00; border: 1px solid #003f00; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; color: #10b981; }
        .error { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; color: #ef4444; }
        .blocked { background: #1a1a00; border: 1px solid #3f3f00; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; color: #f59e0b; }
        .flag { background: #1a1a00; border: 1px solid #3f3f00; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; color: #f59e0b; font-family: monospace; }
        .info { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>登录页面（综合防御）</h1>
        <p class="subtitle">WAF 绕过 + 分布式爆破 | CyberRange Auth Hell Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场有多层防御：验证码、CSRF Token、账户锁定。</p>
            <p>需要综合运用多种绕过技术才能成功暴力破解。</p>
            <p>提示：<code>分析每一层防御的弱点，逐个击破</code></p>
        </div>

        <div class="login-form">
            <h3>用户登录</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label>用户名</label>
                    <input type="text" name="username" placeholder="请输入用户名" required <?php echo $blocked ? 'disabled' : ''; ?>>
                </div>
                <div class="form-group">
                    <label>密码</label>
                    <input type="password" name="password" placeholder="请输入密码" required <?php echo $blocked ? 'disabled' : ''; ?>>
                </div>
                <div class="form-group">
                    <label>验证码</label>
                    <div class="captcha-row">
                        <input type="text" name="captcha" placeholder="请输入验证码" required <?php echo $blocked ? 'disabled' : ''; ?>>
                        <div class="captcha-code"><?php echo $_SESSION['captcha']; ?></div>
                    </div>
                </div>
                <button type="submit" <?php echo $blocked ? 'disabled' : ''; ?>>登录</button>
            </form>
        </div>

        <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($blocked): ?>
        <div class="blocked"><?php echo $blocked; ?></div>
        <?php endif; ?>

        <?php if ($flag): ?>
        <div class="flag">Flag: <?php echo $flag; ?></div>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>虽然有多层防御，但仍存在弱点：</p>
            <p>1. <strong>验证码</strong>：失败后才刷新，可先获取一次再爆破</p>
            <p>2. <strong>CSRF Token</strong>：从页面提取，每次请求获取新 Token</p>
            <p>3. <strong>账户锁定</strong>：5 次失败后锁定 60 秒</p>
            <p>绕过策略：</p>
            <p>1. 使用 Python 脚本自动获取 Token 和验证码</p>
            <p>2. 每次请求前先获取新的 Token</p>
            <p>3. 控制请求频率，避免触发锁定</p>
            <p>4. 或使用分布式爆破（多个 IP 同时尝试）</p>
            <p>flag 格式: <code>CR{...}</code>（管理员登录成功后显示）</p>
        </div>
    </div>
</body>
</html>
