# CSRF 是什么

## 概述

CSRF（Cross-Site Request Forgery，跨站请求伪造）是一种攻击方式，攻击者诱导已登录用户在不知情的情况下执行非预期操作。

## 攻击原理

```
用户 → 登录银行网站 → 获得 Session Cookie
用户 → 访问恶意网站 → 恶意网站自动发起转账请求
浏览器自动携带 Cookie → 银行服务器认为是合法请求 → 转账成功
```

关键点：浏览器会自动携带目标域的 Cookie，服务器无法区分请求是用户主动发起还是被诱导的。

## 攻击条件

1. 用户已登录目标网站（有有效的 Session）
2. 目标网站没有 CSRF 防护机制
3. 攻击者知道请求的参数格式

## 常见攻击场景

- 转账操作
- 修改密码
- 修改邮箱
- 发布内容
- 删除数据

## 防御方法

1. **CSRF Token** — 每个表单包含随机 token，服务器验证
2. **SameSite Cookie** — 设置 `SameSite=Strict` 或 `Lax`
3. **Referer/Origin 检查** — 验证请求来源
4. **双重提交 Cookie** — Cookie 和请求参数中都带 token

## 靶场

- Easy: GET 型 CSRF
- Simple: POST 型 CSRF
- Hard: Token 绕过
- Hell: CSRF + XSS 组合攻击
