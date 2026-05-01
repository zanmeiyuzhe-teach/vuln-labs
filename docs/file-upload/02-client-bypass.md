# 前端验证绕过

## 概述

最简单的文件上传绕过方式。当前验证只在前端 JavaScript 中进行时，可以通过多种方式绕过。

## 漏洞代码

```html
<form action="upload.php" method="POST" enctype="multipart/form-data">
  <input type="file" name="file">
  <button type="submit">上传</button>
</form>

<script>
function checkFile(file) {
  var ext = file.name.split('.').pop().toLowerCase();
  if (['jpg', 'png', 'gif'].indexOf(ext) === -1) {
    alert('只允许上传图片文件');
    return false;
  }
  return true;
}
</script>
```

## 绕过方法

### 1. 禁用 JavaScript

在浏览器中禁用 JavaScript，前端验证不再生效。

### 2. 使用 Burp Suite 拦截

1. 将文件扩展名改为 `.jpg` 通过前端验证
2. 使用 Burp Suite 拦截请求
3. 将扩展名改回 `.php`
4. 转发请求

### 3. 直接发请求

使用 curl 直接发送请求，绕过前端：

```bash
curl -F "file=@shell.php" http://target.com/upload.php
```

### 4. 修改文件名

在 Burp Suite 中修改文件名为 `shell.php`。

## 练习步骤

1. 尝试上传 `.php` 文件 — 被前端拦截
2. 使用 Burp Suite 拦截请求
3. 修改文件名为 `.php`
4. 访问上传的文件
5. 执行命令获取 flag

## flag

在 `/flag` 文件中查找 `CR{...}` 格式的 flag。
