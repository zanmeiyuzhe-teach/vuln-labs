<?php
require_once 'config.php';

session_start();
$_SESSION['username'] = 'victim';
$_SESSION['user_id'] = 2;

$message = '';
$balance = 0;

$result = $conn->query("SELECT balance FROM users WHERE username = 'victim'");
if ($result && $row = $result->fetch_assoc()) {
    $balance = $row['balance'];
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle POST transfer with CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['to']) && isset($_POST['amount'])) {
    // VULNERABLE: Token check is flawed - only checks if token exists, not if it matches
    if (isset($_POST['csrf_token'])) {
        $to = $_POST['to'];
        $amount = floatval($_POST['amount']);

        if ($amount > 0 && $balance >= $amount) {
            $conn->query("UPDATE users SET balance = balance - $amount WHERE username = 'victim'");
            $conn->query("UPDATE users SET balance = balance + $amount WHERE username = '$to'");
            $conn->query("INSERT INTO transfers (from_user, to_user, amount) VALUES ('victim', '$to', $amount)");
            $message = "转账成功！向 $to 转账 ¥$amount";
            $balance -= $amount;
        } elseif ($amount > 0) {
            $message = '余额不足！';
        }
    } else {
        $message = 'CSRF Token 缺失！';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Hard - Token 绕过</title>
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
        .user-info {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;
        }
        .user-info .user { font-size: 1.1rem; font-weight: 600; }
        .user-info .balance { color: #f59e0b; font-size: 1.5rem; font-weight: 700; }
        .transfer-form {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .transfer-form h3 { margin-bottom: 1.5rem; }
        .form-row { display: flex; gap: 1rem; margin-bottom: 1rem; align-items: end; }
        .form-group { flex: 1; }
        .form-group label { display: block; color: #a1a1aa; font-size: 0.9rem; margin-bottom: 0.5rem; }
        input[type="text"], input[type="number"] {
            width: 100%; padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input:focus { outline: none; border-color: #f59e0b; }
        button {
            padding: 0.75rem 1.5rem; background: #f59e0b; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #d97706; }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .message.success { background: #0a1a0a; border: 1px solid #003f00; color: #10b981; }
        .message.error { background: #1a0000; border: 1px solid #3f0000; color: #ef4444; }
        .token-display {
            background: #1e1e2e; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem;
            font-family: monospace; font-size: 0.85rem; color: #a1a1aa; word-break: break-all;
        }
        .info { background: #1a0a00; border: 1px solid #3f2000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>在线银行系统</h1>
        <p class="subtitle">用户: victim | CyberRange CSRF Hard Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场有 CSRF Token 保护，但实现有漏洞。</p>
            <p>Token 存在但验证不严格，攻击者可以绕过保护。</p>
            <p>仔细观察 Token 验证逻辑，找到可以利用的弱点。</p>
        </div>

        <div class="user-info">
            <span class="user">当前用户: victim</span>
            <span class="balance">余额: ¥<?php echo number_format($balance, 2); ?></span>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="token-display">
            CSRF Token: <?php echo $_SESSION['csrf_token']; ?>
        </div>

        <div class="transfer-form">
            <h3>转账</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>收款人</label>
                        <input type="text" name="to" placeholder="输入收款人用户名" required>
                    </div>
                    <div class="form-group">
                        <label>金额</label>
                        <input type="number" name="amount" placeholder="输入转账金额" min="0.01" step="0.01" required>
                    </div>
                    <button type="submit">转账</button>
                </div>
            </form>
        </div>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>虽然有 CSRF Token，但验证逻辑有问题：</p>
            <p>服务器只检查 Token 是否存在（<code>isset($_POST['csrf_token'])</code>），不验证 Token 是否匹配。</p>
            <p>攻击者可以提交任意值作为 Token，只要不为空即可。</p>
            <p>正确的做法应该是：<code>if ($_POST['csrf_token'] === $_SESSION['csrf_token'])</code></p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>
</body>
</html>
