# RCE 是什么

## 概述

RCE（Remote Code Execution，远程代码执行）是一种高危漏洞，攻击者可以在服务器上执行任意命令或代码。

## 攻击原理

```
用户输入 → 未经过滤 → 直接传入 shell_exec() / system() / eval()
→ 服务器执行攻击者构造的命令
```

## 危险函数

### PHP 命令执行

```php
system($cmd)       // 执行命令并输出
exec($cmd)         // 执行命令
shell_exec($cmd)   // 执行命令并返回输出
passthru($cmd)     // 执行命令并直接输出
popen($cmd, 'r')  // 执行命令并打开管道
proc_open($cmd)    // 执行命令并打开进程
```

### PHP 代码执行

```php
eval($code)           // 执行 PHP 代码
assert($code)         // 断言（PHP 7.x 前可执行代码）
preg_replace('/e', $code)  // 正则替换（PHP 7 前）
create_function($args, $code)  // 创建匿名函数
call_user_func($func, $args)  // 回调函数
```

## 命令拼接

```bash
; command    # 分号，顺序执行
| command    # 管道，前一个命令的输出作为输入
|| command   # 或，前一个命令失败时执行
&& command   # 与，前一个命令成功时执行
`command`    # 反引号，命令替换
$(command)   # 命令替换
```

## 防御方法

1. 不要将用户输入传入命令执行函数
2. 使用白名单验证输入
3. 使用 `escapeshellarg()` / `escapeshellcmd()` 转义
4. 使用参数化 API 而非字符串拼接

## 靶场

- Easy: 直接命令执行（ping 功能）
- Simple: 命令拼接绕过（过滤部分符号）
- Hard: eval() 代码执行
- Hell: 多功能工具综合利用
