# Token 防暴力破解绕过

## 概述

一些应用使用 Token 防止暴力破解，要求每次登录请求携带有效的 Token。但实现不当仍可被绕过。

## 漏洞实现

```php
<?php
session_start();
$token = $_SESSION['token'];
$request_token = $_POST['token'];

if ($token !== $request_token) {
    die('Invalid token');
}

// 处理登录...
?>
```

## 绕过方法

### 1. Token 不绑定 Session

```php
// Token 存在全局变量中
if ($_POST['token'] === $GLOBALS['token']) {
    // 通过
}
```

绕过：使用自己的 Token。

### 2. Token 可预测

```php
// Token 基于时间生成
$token = md5(time());
```

绕过：用相同算法生成 Token。

### 3. Token 不过期

Token 生成后永不过期，可以重复使用。

绕过：获取一次 Token，之后所有请求使用同一个值。

### 4. HEAD 方法绕过

某些框架对 HEAD 方法不检查 Token。

```bash
curl -X HEAD http://target.com/login
```

### 5. 并发请求

同时发送多个请求，Token 检查存在竞争条件。

## 练习步骤

1. 尝试暴力破解 — 被 Token 阻止
2. 分析 Token 机制
3. 尝试重用 Token — 成功
4. 使用单个 Token 进行暴力破解

## flag

破解成功后在页面中查找 `CR{...}` 格式的 flag。
