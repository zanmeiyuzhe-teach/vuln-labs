# 垂直越权

## 概述

垂直越权允许低权限用户访问高权限功能。常见于管理员功能没有正确验证用户角色时。

## 漏洞代码

```php
<?php
$action = $_GET['action'];

if ($action === 'admin') {
    // 没有验证用户是否是管理员
    $sql = "SELECT * FROM users";
    $result = mysqli_query($conn, $sql);
    // 显示所有用户信息
}
?>
```

## 攻击方式

### 1. 直接访问管理功能

```
/admin/users        →  管理员页面
/admin/delete?id=1  →  删除用户
/admin/settings     →  修改设置
```

### 2. 修改请求参数

```
正常: /profile?update=true
攻击: /profile?update=true&role=admin
```

### 3. 修改 Cookie

```
Cookie: role=user  →  Cookie: role=admin
```

### 4. 修改隐藏表单字段

```html
<input type="hidden" name="role" value="user">
→
<input type="hidden" name="role" value="admin">
```

## 关键点

- 服务器没有验证用户角色
- 功能入口没有权限检查
- 依赖客户端数据（Cookie、隐藏字段）控制权限

## 练习步骤

1. 以普通用户身份登录
2. 尝试访问管理员功能
3. 尝试修改参数绕过权限检查
4. 获取管理员权限或敏感信息

## flag

在管理员页面或敏感数据中查找 `CR{...}` 格式的 flag。
