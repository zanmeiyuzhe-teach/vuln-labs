# 越权漏洞

## 概述

越权漏洞（Privilege Escalation）发生在应用程序没有正确验证用户权限时，允许用户访问或操作超出其权限范围的资源。

## 类型

### 水平越权

同级别用户之间互相访问。普通用户 A 可以访问普通用户 B 的数据。

```
用户 A 访问 /profile?id=1  →  看到用户 A 的信息（正常）
用户 A 访问 /profile?id=2  →  看到用户 B 的信息（越权）
```

### 垂直越权

低权限用户访问高权限功能。普通用户可以执行管理员操作。

```
普通用户访问 /admin  →  被拒绝（正常）
普通用户访问 /admin?action=delete&id=1  →  删除成功（越权）
```

## 常见漏洞点

### 1. ID 参数

```
/profile?id=1        # 用户自己的信息
/profile?id=2        # 其他用户的信息
/orders?id=100       # 自己的订单
/orders?id=101       # 别人的订单
```

### 2. 功能路径

```
/admin/users         # 管理员功能
/admin/delete?id=1   # 删除用户
/api/v1/users        # 用户列表 API
```

### 3. Cookie/Session 修改

```
Cookie: role=user    → Cookie: role=admin
Cookie: uid=1        → Cookie: uid=2
```

## 防御方法

1. 服务器端验证用户权限，不依赖客户端数据
2. 使用不可预测的 ID（UUID 而非自增 ID）
3. 对每个操作进行权限检查
4. 使用 RBAC（基于角色的访问控制）

## 靶场

- Easy: 水平越权（修改 ID 参数）
- Simple: 垂直越权（访问管理员功能）
- Hard: IDOR（遍历订单 ID）
- Hell: 综合越权链
