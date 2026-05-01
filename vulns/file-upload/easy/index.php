<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload Easy - 客户端校验绕过</title>
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
        input[type="file"]:hover { border-color: #10b981; }
        button {
            padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 1rem;
        }
        button:hover { background: #059669; }
        .message { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .message.success { background: #0a1a0a; border: 1px solid #003f00; color: #10b981; }
        .message.error { background: #1a0000; border: 1px solid #3f0000; color: #ef4444; }
        .files-list { margin-bottom: 2rem; }
        .files-list h3 { color: #a1a1aa; font-size: 0.9rem; margin-bottom: 1rem; }
        .file-item {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 0.75rem 1rem; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;
        }
        .file-item .name { font-family: monospace; }
        .file-item .link { color: #10b981; text-decoration: none; font-size: 0.85rem; }
        .file-item .link:hover { text-decoration: underline; }
        .info { background: #0a1a0a; border: 1px solid #003f00; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #10b981; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>头像上传系统</h1>
        <p class="subtitle">上传你的头像 | CyberRange File Upload Easy Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个客户端校验绕过靶场。文件类型验证只在前端 JavaScript 进行。</p>
            <p>攻击者可以禁用 JavaScript 或直接修改请求来绕过验证。</p>
            <p>试试上传一个 <code>.php</code> 文件，但先修改扩展名为 <code>.jpg</code>，然后用 Burp Suite 改回来。</p>
        </div>

        <div class="upload-form">
            <h3>上传头像</h3>
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="form-row">
                    <label>选择文件 (只允许 .jpg, .png, .gif)</label>
                    <input type="file" name="file" id="fileInput" required>
                </div>
                <button type="submit">上传</button>
            </form>
        </div>

        <?php
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $filename = $file['name'];
            $tmp_name = $file['tmp_name'];

            // VULNERABLE: No server-side validation!
            // Only client-side JavaScript checks file extension
            $target = 'uploads/' . basename($filename);

            if (move_uploaded_file($tmp_name, $target)) {
                $message = "文件上传成功: " . htmlspecialchars($filename);
                $messageType = 'success';

                // Record in database
                require_once 'config.php';
                $conn->query("INSERT INTO files (filename, original_name, file_type, file_size) VALUES ('$target', '$filename', '{$file['type']}', {$file['size']})");
            } else {
                $message = '文件上传失败！';
                $messageType = 'error';
            }
        }
        ?>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php
        // List uploaded files
        $files = glob('uploads/*');
        if ($files):
        ?>
        <div class="files-list">
            <h3>已上传文件</h3>
            <?php foreach ($files as $f): ?>
                <div class="file-item">
                    <span class="name"><?php echo htmlspecialchars(basename($f)); ?></span>
                    <a href="<?php echo htmlspecialchars($f); ?>" class="link" target="_blank">查看</a>
                </div>
            <?php endforeach; }
        ?></div>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>文件上传功能只在前端使用 JavaScript 验证文件类型：</p>
            <p><code>if (!filename.match(/\.(jpg|png|gif)$/i)) { alert('只允许图片'); return false; }</code></p>
            <p>服务器端没有任何验证，直接保存上传的文件。</p>
            <p>攻击者可以：</p>
            <p>1. 禁用浏览器 JavaScript</p>
            <p>2. 使用代理工具（Burp Suite）修改请求</p>
            <p>3. 直接发送 POST 请求</p>
            <p>flag 格式: <code>CR{...}</code></p>
        </div>
    </div>

    <script>
        // Client-side validation (VULNERABLE - can be bypassed)
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            var file = document.getElementById('fileInput').value;
            var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;

            if (!allowedExtensions.exec(file)) {
                alert('只允许上传 .jpg, .png, .gif 文件！');
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
