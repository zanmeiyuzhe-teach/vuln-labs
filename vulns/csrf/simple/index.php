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

// Handle POST transfer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['to']) && isset($_POST['amount'])) {
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
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Simple - POST 型 CSRF</title>
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
        .user-info {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;
        }
        .user-info .user { font-size: 1.1rem; font-weight: 600; }
        .user-info .balance { color: #3b82f6; font-size: 1.5rem; font-weight: 700; }
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
        input:focus { outline: none; border-color: #3b82f6; }
        button {
            padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #2563eb; }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .message.success { background: #0a1a0a; border: 1px solid #003f00; color: #10b981; }
        .message.error { background: #1a0000; border: 1px solid #3f0000; color: #ef4444; }
        .info { background: #0a0a1a; border: 1px solid #00003f; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #3b82f6; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>在线银行系统</h1>
        <p class="subtitle">用户: victim | CyberRange CSRF Simple Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个 POST 型 CSRF 靶场。转账功能使用 POST 请求，比 GET 请求稍微安全一些。</p>
            <p>但攻击者仍然可以构造自动提交的表单来实现 CSRF 攻击。</p>
            <p>需要在攻击页面放置一个隐藏的表单，然后用 JavaScript 自动提交。</p>
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

        <div class="transfer-form">
            <h3>转账</h3>
            <form method="POST">
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
            <h3>攻击示例</h3>
            <p>攻击者可以创建一个包含自动提交表单的恶意页面：</p>
            <p><code>&lt;form id="csrf" action="http://target/simple/" method="POST"&gt;</code></p>
            <p><code>&lt;input name="to" value="attacker"&gt;</code></p>
            <p><code>&lt;input name="amount" value="500"&gt;</code></p>
            <p><code>&lt;/form&gt;&lt;script&gt;document.getElementById('csrf').submit()&lt;/script&gt;</code></p>
            <p>当受害者访问该页面时，表单会自动提交，完成转账。</p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>
</body>
</html>
