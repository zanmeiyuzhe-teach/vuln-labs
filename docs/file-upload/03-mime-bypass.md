# MIME 类型绕过

## 概述

MIME 类型检查验证 HTTP 请求中的 `Content-Type` 头。由于 Content-Type 由客户端设置，可以轻松伪造。

## 漏洞代码

```php
<?php
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$type = $_FILES['file']['type'];

if (!in_array($type, $allowed_types)) {
    die('只允许上传图片文件');
}

move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/' . $_FILES['file']['name']);
?>
```

## 绕过方法

### Burp Suite 修改 Content-Type

1. 上传 `shell.php`
2. 拦截请求
3. 修改 `Content-Type: application/octet-php` 为 `Content-Type: image/jpeg`
4. 转发请求

### curl 指定 Content-Type

```bash
curl -F "file=@shell.php;type=image/jpeg" http://target.com/upload.php
```

## 常见 MIME 类型

```
image/jpeg        .jpg
image/png         .png
image/gif         .gif
application/pdf   .pdf
text/plain        .txt
application/octet-stream  .bin
```

## 练习步骤

1. 尝试上传 `.php` 文件 — 被 MIME 检查拦截
2. 使用 Burp Suite 修改 Content-Type
3. 将 Content-Type 改为 `image/jpeg`
4. 访问上传的文件
5. 执行命令获取 flag

## flag

在 `/flag` 文件中查找 `CR{...}` 格式的 flag。
