<?php
require_once 'config.php';

session_start();
$_SESSION['user_id'] = 2; // user1
$_SESSION['username'] = 'user1';

$order_id = $_GET['order_id'] ?? '';
$order = null;
$message = '';

if ($order_id) {
    // VULNERABLE: IDOR - No check if order belongs to current user
    $result = $conn->query("SELECT * FROM orders WHERE id = $order_id");
    if ($result && $row = $result->fetch_assoc()) {
        $order = $row;
    }
}

// Handle order action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order) {
    $action = $_POST['action'] ?? '';
    if ($action === 'cancel') {
        $conn->query("UPDATE orders SET status = 'cancelled' WHERE id = $order_id");
        $message = '订单已取消！';
    }
    // Refresh order
    $result = $conn->query("SELECT * FROM orders WHERE id = $order_id");
    if ($result && $row = $result->fetch_assoc()) {
        $order = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privilege Hard - IDOR</title>
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
        .order-search {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .order-search h3 { margin-bottom: 1rem; }
        .form-row { display: flex; gap: 1rem; }
        input[type="text"] {
            flex: 1; padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input:focus { outline: none; border-color: #f59e0b; }
        button {
            padding: 0.75rem 1.5rem; background: #f59e0b; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #d97706; }
        .order-card {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .order-card .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .order-card .order-id { font-size: 1.2rem; font-weight: 600; }
        .order-card .status { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .order-card .status.pending { background: #f59e0b; color: white; }
        .order-card .status.completed { background: #10b981; color: white; }
        .order-card .status.cancelled { background: #ef4444; color: white; }
        .order-card .info { margin-bottom: 1rem; }
        .order-card .info p { color: #a1a1aa; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .order-card .info span { color: #f5f5f5; }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background: #0a1a0a; border: 1px solid #003f00; color: #10b981; }
        .info { background: #1a0a00; border: 1px solid #3f2000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>订单查询系统</h1>
        <p class="subtitle">当前用户: user1 | CyberRange Privilege Hard Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个 IDOR (Insecure Direct Object Reference) 靶场。</p>
            <p>订单查询使用订单 ID 作为参数，但没有验证订单是否属于当前用户。</p>
            <p>攻击者可以通过遍历订单 ID 访问其他用户的订单信息。</p>
            <p>试试: <code>?order_id=1</code> 查看 admin 的订单</p>
        </div>

        <div class="order-search">
            <h3>查询订单</h3>
            <form method="GET">
                <div class="form-row">
                    <input type="text" name="order_id" placeholder="输入订单 ID" value="<?php echo htmlspecialchars($order_id); ?>">
                    <button type="submit">查询</button>
                </div>
            </form>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($order): ?>
        <div class="order-card">
            <div class="header">
                <span class="order-id">订单 #<?php echo $order['id']; ?></span>
                <span class="status <?php echo $order['status']; ?>"><?php echo $order['status']; ?></span>
            </div>
            <div class="info">
                <p>用户 ID: <span><?php echo $order['user_id']; ?></span></p>
                <p>产品: <span><?php echo htmlspecialchars($order['product']); ?></span></p>
                <p>金额: <span>¥<?php echo number_format($order['amount'], 2); ?></span></p>
                <p>创建时间: <span><?php echo htmlspecialchars($order['created_at']); ?></span></p>
            </div>
            <?php if ($order['status'] === 'pending'): ?>
            <form method="POST" style="margin-top: 1rem;">
                <input type="hidden" name="action" value="cancel">
                <button type="submit" style="background: #ef4444;">取消订单</button>
            </form>
            <?php endif; ?>
        </div>
        <?php elseif ($order_id): ?>
        <p style="color: #a1a1aa;">未找到订单</p>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>订单查询直接使用用户输入的 ID：</p>
            <p><code>$result = $conn->query("SELECT * FROM orders WHERE id = $order_id");</code></p>
            <p>没有验证订单是否属于当前用户：</p>
            <p><code>WHERE id = $order_id AND user_id = $_SESSION['user_id']</code></p>
            <p>攻击者可以遍历订单 ID 获取所有用户订单信息，甚至取消其他用户订单。</p>
            <p>flag 格式: <code>CR{...}</code> (在 admin 的订单中)</p>
        </div>
    </div>
</body>
</html>
