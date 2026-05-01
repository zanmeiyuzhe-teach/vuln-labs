<?php
require_once 'config.php';

$message = '';
$comments = [];

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['content'])) {
    $username = $_POST['username'];
    $content = $_POST['content'];

    if (!empty($username) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO comments (username, content) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $content);
        if ($stmt->execute()) {
            $message = '评论发表成功！';
        } else {
            $message = '评论发表失败，请重试。';
        }
        $stmt->close();
    }
}

// Fetch all comments
$result = $conn->query("SELECT username, content, created_at FROM comments ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSS Simple - 留言板</title>
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
        .form-section { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; }
        .form-section h3 { color: #f5f5f5; margin-bottom: 1rem; font-size: 1rem; }
        .form-row { display: flex; gap: 1rem; margin-bottom: 1rem; }
        .form-row:last-child { margin-bottom: 0; }
        input[type="text"], textarea {
            flex: 1; padding: 0.75rem 1rem; background: #0a0a0a; border: 1px solid #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 0.95rem;
        }
        input[type="text"]:focus, textarea:focus { outline: none; border-color: #3b82f6; }
        textarea { min-height: 100px; resize: vertical; }
        button {
            padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 0.95rem;
        }
        button:hover { background: #2563eb; }
        .message { padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
        .message.success { background: #0a1a0a; border: 1px solid #003f00; color: #10b981; }
        .message.error { background: #1a0000; border: 1px solid #3f0000; color: #ef4444; }
        .comments-section { margin-bottom: 2rem; }
        .comments-section h3 { color: #a1a1aa; font-size: 0.9rem; margin-bottom: 1rem; }
        .comment {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 1rem; margin-bottom: 0.75rem;
        }
        .comment-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
        .comment-author { color: #3b82f6; font-weight: 600; font-size: 0.9rem; }
        .comment-time { color: #71717a; font-size: 0.8rem; }
        .comment-content { color: #f5f5f5; font-size: 0.95rem; line-height: 1.5; }
        .info { background: #0a0a1a; border: 1px solid #00003f; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #3b82f6; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>用户留言板</h1>
        <p class="subtitle">分享你的想法 | CyberRange XSS Simple Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个存储型 XSS 靶场。用户提交的评论会被保存到数据库，然后在页面上显示。</p>
            <p>与反射型不同，存储型 XSS 的 payload 会持久化存储，所有访问页面的用户都会受到影响。</p>
            <p>试试在评论中输入: <code>&lt;script&gt;alert(document.cookie)&lt;/script&gt;</code></p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, '成功') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h3>发表评论</h3>
            <form method="POST">
                <div class="form-row">
                    <input type="text" name="username" placeholder="你的用户名" required>
                </div>
                <div class="form-row">
                    <textarea name="content" placeholder="写下你的评论..." required></textarea>
                </div>
                <div class="form-row">
                    <button type="submit">发表评论</button>
                </div>
            </form>
        </div>

        <div class="comments-section">
            <h3>所有评论 (<?php echo count($comments); ?>)</h3>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-header">
                        <span class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></span>
                        <span class="comment-time"><?php echo htmlspecialchars($comment['created_at']); ?></span>
                    </div>
                    <div class="comment-content">
                        <?php echo $comment['content']; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($comments)): ?>
                <p style="color: #a1a1aa; text-align: center; padding: 2rem;">暂无评论，来发表第一条吧！</p>
            <?php endif; ?>
        </div>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>注意评论内容的输出方式：<code>$comment['content']</code> 直接输出，没有经过 <code>htmlspecialchars()</code> 转义。</p>
            <p>而用户名使用了 <code>htmlspecialchars()</code> 转义，所以用户名是安全的。</p>
            <p>攻击者可以注入持久化的恶意脚本，影响所有访问该页面的用户。</p>
            <p>flag 格式: <code>CR{...}</code> (在数据库中)</p>
        </div>
    </div>
</body>
</html>
