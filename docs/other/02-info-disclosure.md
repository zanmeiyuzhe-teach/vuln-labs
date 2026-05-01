# 信息泄露

## 概述

信息泄露（Information Disclosure）指应用程序无意中暴露敏感信息，帮助攻击者了解系统架构和进一步攻击。

## 常见泄露类型

### 1. 错误信息泄露

```php
// 生产环境不应显示详细错误
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

泄露内容：
- 文件路径
- 数据库错误（表名、列名）
- 堆栈跟踪
- 框架版本

### 2. phpinfo() 泄露

```php
phpinfo();
```

泄露内容：
- PHP 版本和配置
- 环境变量（API Key、数据库密码）
- 服务器信息
- 已加载模块

### 3. robots.txt

```
User-agent: *
Disallow: /admin/
Disallow: /backup/
Disallow: /config/
Disallow: /.git/
```

暴露敏感目录路径。

### 4. 目录列表

```php
$files = scandir('.');
foreach ($files as $f) {
    echo $f;
}
```

暴露服务器文件结构。

### 5. 备份文件

```
/config.php.bak
/database.sql
/.git/
/.env
```

### 6. HTTP 响应头

```
Server: Apache/2.4.57 (Ubuntu)
X-Powered-By: PHP/8.2.10
```

暴露服务器版本信息。

## 防御

1. 生产环境关闭详细错误显示
2. 删除 phpinfo() 和测试文件
3. 配置 robots.txt 时注意不要暴露真实路径
4. 禁用目录列表
5. 删除备份文件和 .git 目录
6. 隐藏服务器版本信息

## 练习步骤

1. 尝试访问 `?page=error` — 观察错误信息
2. 尝试访问 `?page=phpinfo` — 查看配置
3. 尝试访问 `?page=robots` — 查看敏感路径
4. 尝试访问 `?page=nonexistent` — 触发目录列表
5. 在泄露信息中查找 flag

## flag

在泄露的环境变量或配置信息中查找 `CR{...}` 格式的 flag。
