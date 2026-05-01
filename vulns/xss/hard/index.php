<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSS Hard - DOM 型 XSS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0a0a0a; color: #f5f5f5; font-family: -apple-system, sans-serif; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { color: #f97316; margin-bottom: 0.5rem; }
        .subtitle { color: #a1a1aa; margin-bottom: 2rem; font-size: 0.9rem; }
        .hint { background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px; padding: 1rem; margin-bottom: 2rem; }
        .hint h3 { color: #f97316; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .hint p { color: #a1a1aa; font-size: 0.85rem; }
        .hint code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
        .profile-card {
            background: #1a1a1a; border: 1px solid #27272a; border-radius: 8px;
            padding: 2rem; margin-bottom: 2rem; text-align: center;
        }
        .profile-card .avatar {
            width: 80px; height: 80px; border-radius: 50%; background: #27272a;
            margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
        }
        .profile-card .name { font-size: 1.5rem; font-weight: 600; margin-bottom: 0.5rem; }
        .profile-card .bio { color: #a1a1aa; font-size: 0.95rem; line-height: 1.5; }
        .profile-card .welcome { color: #f97316; font-size: 0.85rem; margin-bottom: 1rem; }
        .nav-links { display: flex; gap: 1rem; justify-content: center; margin-bottom: 2rem; }
        .nav-links a {
            padding: 0.5rem 1rem; background: #27272a; color: #f5f5f5; text-decoration: none;
            border-radius: 8px; font-size: 0.9rem; transition: all 0.2s;
        }
        .nav-links a:hover { background: #3f3f46; }
        .nav-links a.active { background: #f97316; color: white; }
        .output-area {
            background: #0a0a0a; border: 1px dashed #27272a; border-radius: 8px;
            padding: 1.5rem; margin-bottom: 2rem; min-height: 100px;
        }
        .output-area h4 { color: #a1a1aa; font-size: 0.85rem; margin-bottom: 0.75rem; }
        #output { font-family: monospace; font-size: 0.9rem; color: #10b981; }
        .info { background: #1a0a00; border: 1px solid #3f2000; border-radius: 8px; padding: 1rem; margin-top: 2rem; }
        .info h3 { color: #f97316; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .info p { color: #a1a1aa; font-size: 0.85rem; }
        .info code { background: #27272a; padding: 0.2rem 0.4rem; border-radius: 4px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>用户个人主页</h1>
        <p class="subtitle">查看用户资料 | CyberRange XSS Hard Lab</p>

        <div class="hint">
            <h3>学习目标</h3>
            <p>这是一个 DOM 型 XSS 靶场。页面使用 JavaScript 处理 URL 参数，直接将数据写入 DOM。</p>
            <p>DOM 型 XSS 的特点是：恶意代码不经过服务器，完全在客户端执行。</p>
            <p>注意观察页面 JavaScript 如何处理 URL 中的 <code>#</code> 片段。</p>
        </div>

        <div class="nav-links">
            <a href="#" class="active" onclick="loadProfile('admin')">Admin</a>
            <a href="#" onclick="loadProfile('user1')">User1</a>
            <a href="#" onclick="loadProfile('user2')">User2</a>
        </div>

        <div class="profile-card">
            <div class="avatar" id="avatar">?</div>
            <div class="welcome" id="welcome"></div>
            <div class="name" id="username">加载中...</div>
            <div class="bio" id="bio"></div>
        </div>

        <div class="output-area">
            <h4>URL 解析结果:</h4>
            <div id="output"></div>
        </div>

        <div class="info">
            <h3>漏洞分析</h3>
            <p>查看页面源代码，找到处理 URL 片段的 JavaScript 代码。</p>
            <p>页面使用 <code>location.hash</code> 获取 URL 片段，然后使用 <code>innerHTML</code> 直接写入 DOM。</p>
            <p>试试: <code>#&lt;img src=x onerror=alert(1)&gt;</code></p>
            <p>或者: <code>#&lt;svg onload=alert(1)&gt;</code></p>
            <p>flag 格式: <code>CR{...}</code> (在 JavaScript 代码中)</p>
        </div>
    </div>

    <script>
        // User database (simulated)
        const users = {
            'admin': { name: '管理员', bio: '系统管理员，负责维护平台安全。', avatar: 'A' },
            'user1': { name: '用户一', bio: '网络安全爱好者，正在学习 XSS 漏洞。', avatar: 'U' },
            'user2': { name: '用户二', bio: '前端开发工程师，对 Web 安全感兴趣。', avatar: 'U' }
        };

        // VULNERABLE: DOM-based XSS
        // The hash fragment is directly used to look up user and innerHTML is used to render
        function loadProfile(userId) {
            const user = users[userId];
            if (user) {
                document.getElementById('avatar').textContent = user.avatar;
                document.getElementById('username').textContent = user.name;
                document.getElementById('bio').textContent = user.bio;
                document.getElementById('welcome').textContent = '欢迎访问 ' + user.name + ' 的主页';
                document.getElementById('output').textContent = '用户: ' + userId;
            } else {
                // VULNERABLE: This is where the DOM XSS happens
                // The userId from URL hash is directly inserted into DOM
                document.getElementById('username').innerHTML = userId;
                document.getElementById('bio').textContent = '用户不存在';
                document.getElementById('welcome').textContent = '';
                document.getElementById('output').innerHTML = '未知用户: ' + userId;
            }

            // Update active link
            document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
        }

        // VULNERABLE: Process URL hash fragment
        // This is the entry point for DOM-based XSS
        function processHash() {
            const hash = location.hash.slice(1); // Remove #
            if (hash) {
                const userId = decodeURIComponent(hash);
                loadProfile(userId);
            }
        }

        // Listen for hash changes
        window.addEventListener('hashchange', processHash);

        // Process on page load
        if (location.hash) {
            processHash();
        }

        // Hidden flag in JavaScript (for the challenge)
        // CR{xss_dom_hash_fragment}
    </script>
</body>
</html>
