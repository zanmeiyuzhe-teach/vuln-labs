<?php
require_once 'config.php';

session_start();
$_SESSION['user_id'] = 2; // user1
$_SESSION['username'] = 'user1';
$_SESSION['role'] = 'user';

$message = '';
$action = $_GET['action'] ?? 'dashboard';

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'admin') {
    $config_key = $_POST['config_key'] ?? '';
    $config_value = $_POST['config_value'] ?? '';

    if (!empty($config_key) && !empty($config_value)) {
        // VULNERABLE: No role check for admin actions
        $conn->query("UPDATE admin_config SET config_value = '$config_value' WHERE config_key = '$config_key'");
        $message = '配置已更新！';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privilege Simple - 垂直越权</title>
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
        .nav { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .nav a {
            padding: 0.5rem 1rem; background: #27272a; color: #f5f5f5; text-decoration: none;
            border-radius: 8px; font-size: 0.9rem;
        }
        .nav a:hover { background: #3f3f46; }
        .nav a.active { background: #3b82f6; color: white; }
        .content {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .content h2 { margin-bottom: 1rem; }
        .content p { color: #a1a1aa; line-height: 1.6; }
        .config-item {
            background: #0a0a0a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;
        }
        .config-item .key { font-weight: 600; color: #f5f5f5; }
        .config-item .value { color: #10b981; font-family: monospace; }
        .admin-form {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .admin-form h3 { margin-bottom: 1.5rem; }
        .form-row { display: flex; gap: 1rem; margin-bottom: 1rem; }
        input[type="text"] {
            flex: 1; padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 1rem;
        }
        input:focus { outline: none; border-color: #3b82f6; }
        button {
            padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #2563eb; }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background: #0a1a0a; border: 1px solid #003f00; color: #10b981; }
        .info { background: #0a0a1a; border: 1px solid #00003f; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #3b82f6; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>系统管理面板</h1>
        <p class="subtitle">当前用户: user1 (普通用户) | CyberRange Privilege Simple Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个垂直越权靶场。系统没有验证用户角色，普通用户可以访问管理员功能。</p>
            <p>管理员页面通常通过 URL 路径区分，但没有检查用户权限。</p>
            <p>试试: <code>?action=admin</code> 访问管理员面板</p>
        </div>

        <div class="nav">
            <a href="?action=dashboard" class="<?php echo $action === 'dashboard' ? 'active' : ''; ?>">控制台</a>
            <a href="?action=profile" class="<?php echo $action === 'profile' ? 'active' : ''; ?>">个人资料</a>
            <a href="?action=admin" class="<?php echo $action === 'admin' ? 'active' : ''; ?>">系统配置</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="content">
            <?php
            switch ($action) {
                case 'dashboard':
                    echo '<h2>控制台</h2>';
                    echo '<p>欢迎回来，user1！</p>';
                    echo '<p>你有 2 个待处理的订单。</p>';
                    break;

                case 'profile':
                    echo '<h2>个人资料</h2>';
                    echo '<p>用户名: user1</p>';
                    echo '<p>邮箱: user1@cyberrange.local</p>';
                    echo '<p>角色: 普通用户</p>';
                    break;

                case 'admin':
                    // VULNERABLE: No role check!
                    echo '<h2>系统配置</h2>';
                    echo '<p style="color: #ef4444; margin-bottom: 1rem;">这是管理员功能，但你成功访问了！</p>';

                    // Show config
                    $result = $conn->query("SELECT * FROM admin_config");
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="config-item">';
                            echo '<span class="key">' . htmlspecialchars($row['config_key']) . '</span>';
                            echo '<span class="value">' . htmlspecialchars($row['config_value']) . '</span>';
                            echo '</div>';
                        }
                    }
                    break;
            }
            ?>
        </div>

        <?php if ($action === 'admin'): ?>
        <div class="admin-form">
            <h3>修改配置</h3>
            <form method="POST">
                <div class="form-row">
                    <input type="text" name="config_key" placeholder="配置项名称">
                    <input type="text" name="config_value" placeholder="配置值">
                    <button type="submit">保存</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>系统根据 URL 参数 <code>action</code> 显示不同页面，但没有验证用户角色：</p>
            <p><code>switch ($action) { case 'admin': ... }</code></p>
            <p>没有检查 <code>$_SESSION['role'] === 'admin'</code></p>
            <p>攻击者可以直接访问 <code>?action=admin</code> 获取管理员权限</p>
            <p>flag 格式: <code>CR{...}</code> (在系统配置中)</p>
        </div>
    </div>
</body>
</html>
