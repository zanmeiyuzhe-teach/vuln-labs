<?php
require_once 'config.php';

session_start();
$_SESSION['user_id'] = 2; // user1
$_SESSION['username'] = 'user1';
$_SESSION['role'] = 'user';

$page = $_GET['page'] ?? 'home';
$user_id = $_GET['user_id'] ?? $_SESSION['user_id'];
$message = '';

// Handle different pages
switch ($page) {
    case 'profile':
        // VULNERABLE 1: Horizontal privilege escalation
        $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
        $profile_user = $result ? $result->fetch_assoc() : null;
        break;

    case 'admin':
        // VULNERABLE 2: Vertical privilege escalation (no role check)
        $users = $conn->query("SELECT * FROM users");
        $configs = $conn->query("SELECT * FROM admin_config");
        break;

    case 'orders':
        // VULNERABLE 3: IDOR
        $order_id = $_GET['order_id'] ?? '';
        if ($order_id) {
            $order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
        }
        $my_orders = $conn->query("SELECT * FROM orders WHERE user_id = {$_SESSION['user_id']}");
        break;

    case 'update_role':
        // VULNERABLE 4: Mass assignment / privilege escalation via parameter
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_role = $_POST['role'] ?? 'user';
            $target_id = $_POST['user_id'] ?? $_SESSION['user_id'];
            // No validation - user can change their own role!
            $conn->query("UPDATE users SET role = '$new_role' WHERE id = $target_id");
            if ($target_id == $_SESSION['user_id']) {
                $_SESSION['role'] = $new_role;
            }
            $message = "用户角色已更新为: $new_role";
        }
        break;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privilege Hell - 越权链</title>
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
        .nav { display: flex; gap: 0.5rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .nav a {
            padding: 0.5rem 1rem; background: #27272a; color: #f5f5f5; text-decoration: none;
            border-radius: 8px; font-size: 0.85rem;
        }
        .nav a:hover { background: #3f3f46; }
        .nav a.active { background: #ef4444; color: white; }
        .content {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .content h2 { margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #27272a; }
        th { color: #a1a1aa; font-size: 0.85rem; }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background: #0a1a0a; border: 1px solid #003f00; color: #10b981; }
        .role-form {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .role-form h3 { margin-bottom: 1rem; }
        .form-row { display: flex; gap: 1rem; }
        input[type="text"] {
            flex: 1; padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input:focus { outline: none; border-color: #ef4444; }
        button {
            padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #dc2626; }
        .info { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>系统管理中心</h1>
        <p class="subtitle">当前用户: user1 (<?php echo $_SESSION['role']; ?>) | CyberRange Privilege Hell Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场包含多个越权漏洞，需要组合利用才能获取 flag。</p>
            <p>包含：水平越权、垂直越权、IDOR、参数篡改等漏洞。</p>
            <p>你需要找到所有漏洞并组合利用它们。</p>
        </div>

        <div class="nav">
            <a href="?page=home" class="<?php echo $page === 'home' ? 'active' : ''; ?>">首页</a>
            <a href="?page=profile&user_id=<?php echo $_SESSION['user_id']; ?>" class="<?php echo $page === 'profile' ? 'active' : ''; ?>">个人资料</a>
            <a href="?page=orders" class="<?php echo $page === 'orders' ? 'active' : ''; ?>">我的订单</a>
            <a href="?page=admin" class="<?php echo $page === 'admin' ? 'active' : ''; ?>">系统管理</a>
            <a href="?page=update_role" class="<?php echo $page === 'update_role' ? 'active' : ''; ?>">角色管理</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="content">
            <?php
            switch ($page) {
                case 'home':
                    echo '<h2>欢迎回来</h2>';
                    echo '<p>你好，user1！你的角色是: ' . $_SESSION['role'] . '</p>';
                    echo '<p>这是一个包含多个越权漏洞的综合靶场。</p>';
                    break;

                case 'profile':
                    if ($profile_user) {
                        echo '<h2>用户资料</h2>';
                        echo '<p>用户 ID: ' . $profile_user['id'] . '</p>';
                        echo '<p>用户名: ' . htmlspecialchars($profile_user['username']) . '</p>';
                        echo '<p>邮箱: ' . htmlspecialchars($profile_user['email']) . '</p>';
                        echo '<p>角色: ' . $profile_user['role'] . '</p>';
                        echo '<p>个人资料: ' . htmlspecialchars($profile_user['profile']) . '</p>';
                    }
                    break;

                case 'admin':
                    echo '<h2>系统管理</h2>';
                    echo '<p style="color: #ef4444;">你成功访问了管理员面板！</p>';

                    if ($users) {
                        echo '<h3 style="margin-top: 1rem;">用户列表</h3>';
                        echo '<table><tr><th>ID</th><th>用户名</th><th>角色</th></tr>';
                        while ($u = $users->fetch_assoc()) {
                            echo '<tr><td>' . $u['id'] . '</td><td>' . htmlspecialchars($u['username']) . '</td><td>' . $u['role'] . '</td></tr>';
                        }
                        echo '</table>';
                    }

                    if ($configs) {
                        echo '<h3 style="margin-top: 1rem;">系统配置</h3>';
                        while ($c = $configs->fetch_assoc()) {
                            echo '<p>' . htmlspecialchars($c['config_key']) . ': <code>' . htmlspecialchars($c['config_value']) . '</code></p>';
                        }
                    }
                    break;

                case 'orders':
                    if ($order) {
                        echo '<h2>订单详情</h2>';
                        echo '<p>订单 ID: ' . $order['id'] . '</p>';
                        echo '<p>用户 ID: ' . $order['user_id'] . '</p>';
                        echo '<p>产品: ' . htmlspecialchars($order['product']) . '</p>';
                        echo '<p>金额: ¥' . number_format($order['amount'], 2) . '</p>';
                    }

                    if ($my_orders) {
                        echo '<h3 style="margin-top: 1rem;">我的订单</h3>';
                        echo '<table><tr><th>ID</th><th>产品</th><th>金额</th><th>状态</th></tr>';
                        while ($o = $my_orders->fetch_assoc()) {
                            echo '<tr><td>' . $o['id'] . '</td><td>' . htmlspecialchars($o['product']) . '</td><td>¥' . number_format($o['amount'], 2) . '</td><td>' . $o['status'] . '</td></tr>';
                        }
                        echo '</table>';
                    }
                    break;

                case 'update_role':
                    echo '<h2>角色管理</h2>';
                    echo '<p>使用此功能可以修改用户角色。</p>';
                    break;
            }
            ?>
        </div>

        <?php if ($page === 'update_role'): ?>
        <div class="role-form">
            <h3>修改用户角色</h3>
            <form method="POST">
                <div class="form-row">
                    <input type="text" name="user_id" placeholder="用户 ID" value="<?php echo $_SESSION['user_id']; ?>">
                    <input type="text" name="role" placeholder="新角色 (user/admin)">
                    <button type="submit">更新</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="info">
            <h3>攻击链</h3>
            <p><strong>步骤 1:</strong> 发现角色管理功能，修改自己的角色为 admin</p>
            <p><code>POST /?page=update_role</code> <code>user_id=2&role=admin</code></p>
            <p><strong>步骤 2:</strong> 访问管理员面板 <code>?page=admin</code> 获取敏感信息</p>
            <p><strong>步骤 3:</strong> 使用 IDOR 访问 admin 的订单 <code>?page=orders&order_id=1</code></p>
            <p><strong>步骤 4:</strong> 使用水平越权查看 admin 资料 <code>?page=profile&user_id=1</code></p>
            <p>flag 格式: <code>CR{...}</code> (在系统配置或 admin 资料中)</p>
        </div>
    </div>
</body>
</html>
