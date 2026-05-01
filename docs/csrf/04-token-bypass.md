# CSRF Token 绕过

## 概述

CSRF Token 是最常见的防御方式，但实现不当仍可被绕过。

## 常见绕过方法

### 1. Token 不验证值

```php
<?php
// 只检查 token 是否存在，不验证值
if (isset($_POST['csrf_token'])) {
    // 执行操作
}
?>
```

绕过：随便填一个 token 值即可。

### 2. Token 与 Session 无关

```php
<?php
// token 存在全局变量中，所有用户共享
if ($_POST['csrf_token'] === $GLOBALS['csrf_token']) {
    // 执行操作
}
?>
```

绕过：使用自己的 token 值。

### 3. Token 通过 GET 参数传递

```
/profile?csrf_token=abc123&email=attacker@evil.com
```

绕过：Referer 检查不足时，可以把 token 放在 URL 中。

### 4. Token 可预测

```php
<?php
// token 基于用户名生成，可预测
$token = md5($username);
?>
```

绕过：用相同算法生成 token。

### 5. HEAD 方法绕过

某些框架只对 POST 方法检查 token，可以用 HEAD 方法发送请求。

## 练习步骤

1. 查看表单中的 CSRF token
2. 尝试不带 token 提交 — 被拦截
3. 尝试带任意 token 提交 — 成功（说明只检查存在性）
4. 构造恶意请求

## flag

在操作成功页面中查找 `CR{...}` 格式的 flag。
