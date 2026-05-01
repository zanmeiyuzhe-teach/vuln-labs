<?php
require_once 'config.php';

// Simulate logged-in user
session_start();
$_SESSION['user_id'] = 2; // user1
$_SESSION['username'] = 'user1';
$_SESSION['role'] = 'user';

$user_id = $_GET['id'] ?? $_SESSION['user_id'];
$user = null;
$message = '';

// Get user info
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
if ($result && $row = $result->fetch_assoc()) {
    $user = $row;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile = $_POST['profile'] ?? '';
    // VULNERABLE: No check if user_id matches session user_id
    $conn->query("UPDATE users SET profile = '$profile' WHERE id = $user_id");
    $message = '个人资料已更新！';
    // Refresh user data
    $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    if ($result && $row = $result->fetch_assoc()) {
        $user = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privilege Easy - 水平越权</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #10b981; margin-bottom: 0.5rem; }
        .subtitle { color: #a1a1aa; margin-bottom: 2rem; font-size: 0.9rem; }
        .hint { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .hint h3 { color: #10b981; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .hint p { color: #a1a1aa; font-size: 0.85rem; }
        .hint code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .user-card {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .user-card .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .user-card .username { font-size: 1.5rem; font-weight: 600; }
        .user-card .role { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .user-card .role.user { background: #3b82f6; color: white; }
        .user-card .role.admin { background: #ef4444; color: white; }
        .user-card .info { margin-bottom: 1rem; }
        .user-card .info p { color: #a1a1aa; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .user-card .info span { color: #f5f5f5; }
        .profile-form {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .profile-form h3 { margin-bottom: 1rem; }
        textarea {
            width: 100%; padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 0.95rem; min-height: 100px; resize: vertical;
        }
        textarea:focus { outline: none; border-color: #10b981; }
        button {
            padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem; margin-top: 1rem;
        }
        button:hover { background: #059669; }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background: #0a1a0a; border: 1px solid #003f00; color: #10b981; }
        .info { background: #0a1a0a; border: 1px solid #003f00; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #10b981; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>用户个人中心</h1>
        <p class="subtitle">当前用户: user1 | CyberRange Privilege Easy Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个水平越权靶场。用户可以通过修改 URL 中的 <code>id</code> 参数访问其他用户的资料。</p>
            <p>系统没有验证当前用户是否有权限访问请求的用户数据。</p>
            <p>试试: <code>?id=1</code> 访问 admin 的资料</p>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($user): ?>
        <div class="user-card">
            <div class="header">
                <span class="username"><?php echo htmlspecialchars($user['username']); ?></span>
                <span class="role <?php echo $user['role']; ?>"><?php echo $user['role']; ?></span>
            </div>
            <div class="info">
                <p>用户 ID: <span><?php echo $user['id']; ?></span></p>
                <p>邮箱: <span><?php echo htmlspecialchars($user['email']); ?></span></p>
                <p>个人资料: <span><?php echo htmlspecialchars($user['profile']); ?></span></p>
            </div>
        </div>

        <div class="profile-form">
            <h3>修改个人资料</h3>
            <form method="POST">
                <textarea name="profile" placeholder="输入个人资料..."><?php echo htmlspecialchars($user['profile']); ?></textarea>
                <button type="submit">保存</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>URL 中的 <code>id</code> 参数直接用于数据库查询：</p>
            <p><code>$user_id = $_GET['id'] ?? $_SESSION['user_id'];</code></p>
            <p>没有验证 <code>$user_id</code> 是否等于当前登录用户的 ID。</p>
            <p>攻击者可以修改 <code>id</code> 参数访问任意用户的数据。</p>
            <p>flag 格式: <code>CR{...}</code> (在 admin 的资料中)</p>
        </div>
    </div>
</body>
</html>
