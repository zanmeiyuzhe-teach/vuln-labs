<?php
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    $file_type = $file['type'];

    // Multiple validation layers
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

    // Check MIME type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

    // Check file header
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);

    // Check for PHP tags in file content
    $content = file_get_contents($tmp_name);
    $has_php = (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false || strpos($content, '<script') !== false);

    if (!in_array($ext, $allowed_exts)) {
        $message = '不允许的文件扩展名！';
        $messageType = 'error';
    } elseif (!in_array($file_type, $allowed_types)) {
        $message = '不允许的 MIME 类型！';
        $messageType = 'error';
    } elseif (!in_array($mime, $allowed_types)) {
        $message = '文件头验证失败！';
        $messageType = 'error';
    } elseif ($has_php) {
        $message = '检测到 PHP 代码，上传被拒绝！';
        $messageType = 'error';
    } else {
        // VULNERABLE: .htaccess can be uploaded to override Apache config
        $target = 'uploads/' . basename($filename);
        if (move_uploaded_file($tmp_name, $target)) {
            $message = "文件上传成功: " . htmlspecialchars($filename);
            $messageType = 'success';

            require_once 'config.php';
            $conn->query("INSERT INTO files (filename, original_name, file_type, file_size) VALUES ('$target', '$filename', '$file_type', {$file['size']})");
        } else {
            $message = '文件上传失败！';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload Hell - 综合绕过</title>
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
        .upload-form {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem;
        }
        .upload-form h3 { margin-bottom: 1.5rem; }
        .form-row { margin-bottom: 1rem; }
        .form-row label { display: block; color: #a1a1aa; font-size: 0.9rem; margin-bottom: 0.5rem; }
        input[type="file"] {
            width: 100%; padding: 1rem; background: #0a0a0a; border: 1px dashed #27272a;
            color: #f5f5f5; border-radius: 8px; font-size: 0.9rem;
        }
        input[type="file"]:hover { border-color: #ef4444; }
        button {
            padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #dc2626; }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .message.success { background: #0a1a0a; border: 1px solid #003f00; color: #10b981; }
        .message.error { background: #1a0000; border: 1px solid #3f0000; color: #ef4444; }
        .info { background: #1a0000; border: 1px solid #3f0000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #ef4444; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>头像上传系统</h1>
        <p class="subtitle">上传你的头像 (终极防护) | CyberRange File Upload Hell Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场有最严格的验证：扩展名、MIME 类型、文件头、内容检查。</p>
            <p>直接上传 PHP 文件会被拦截，需要使用高级技术绕过。</p>
            <p>提示：Apache 会执行 <code>.htaccess</code> 文件中的指令...</p>
        </div>

        <div class="upload-form">
            <h3>上传头像</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <label>选择文件 (严格验证)</label>
                    <input type="file" name="file" required>
                </div>
                <button type="submit">上传</button>
            </form>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php
        $files = glob('uploads/*');
        if ($files):
        ?>
        <div style="margin-bottom: 2rem;">
            <h3 style="color: #a1a1aa; font-size: 0.9rem; margin-bottom: 1rem;">已上传文件</h3>
            <?php foreach ($files as $f): ?>
                <div style="background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-family: monospace;"><?php echo htmlspecialchars(basename($f)); ?></span>
                    <a href="<?php echo htmlspecialchars($f); ?>" style="color: #ef4444; text-decoration: none; font-size: 0.85rem;" target="_blank">查看</a>
                </div>
            <?php endforeach; }
        ?></div>

        <div class="info">
            <h3>攻击链</h3>
            <p><strong>步骤 1:</strong> 上传 <code>.htaccess</code> 文件，内容：</p>
            <p><code>AddType application/x-httpd-php .jpg</code></p>
            <p>这会让 Apache 把 .jpg 文件当作 PHP 执行</p>
            <p><strong>步骤 2:</strong> 上传包含 PHP 代码的 <code>shell.jpg</code> 文件</p>
            <p>因为有图片头和正确的扩展名，会通过所有验证</p>
            <p><strong>步骤 3:</strong> 访问 <code>uploads/shell.jpg?cmd=cat /flag</code></p>
            <p>Apache 会将其作为 PHP 执行，实现 RCE</p>
            <p>flag 格式: <code>CR{...}</code> (在 /flag 文件中)</p>
        </div>
    </div>
</body>
</html>
