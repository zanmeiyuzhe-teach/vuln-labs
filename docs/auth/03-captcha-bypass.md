# 验证码绕过

## 概述

验证码（CAPTCHA）用于防止自动化攻击，但实现不当可以被绕过。

## 常见绕过方法

### 1. 验证码前端生成

```javascript
// 验证码在前端生成，完全可预测
var captcha = Math.random().toString(36).substring(7);
```

绕过：直接不带验证码或带任意值。

### 2. 验证码不刷新

验证码生成后不更新，可以重复使用同一个验证码。

绕过：先获取一次验证码，之后所有请求使用同一个值。

### 3. 验证码在 Cookie 中

```
Cookie: captcha=abc123
```

绕过：直接在 Cookie 中设置正确的验证码。

### 4. 验证码在响应头中

```
X-Captcha: abc123
```

绕过：从响应头中提取验证码。

### 5. 无验证码时直接放行

```php
if (empty($_POST['captcha'])) {
    // 不验证
}
```

绕过：不提交验证码字段。

### 6. OCR 识别

使用 OCR 工具自动识别简单验证码：

```python
from PIL import Image
import pytesseract

img = Image.open('captcha.png')
text = pytesseract.image_to_string(img)
```

## 练习步骤

1. 尝试暴力破解 — 被验证码阻止
2. 分析验证码机制
3. 找到绕过方法
4. 成功暴力破解

## flag

破解成功后在页面中查找 `CR{...}` 格式的 flag。
