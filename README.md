DIServer Framework
=======================

## 简介

+ 一个由swoole_server提供网络服务，ThinkPHP提供ORM等其它工具的TCP/UDP网络服务框架。
+ 这个文档暂时没写完，Demo还在整理中，暂时来说这个东西还不算能用。
+ 我会尝试整理出[wiki](https://github.com/szyhf/DIServer-Framework/wiki)

## 特性

- 使用了swoole扩展实现网络通讯，改进过的ThinkPHP作为服务支持；
- 若已有过ThinkPHP的使用基础，可以快速上手。

## 环境

- php5.6+ 
- Swoole1.7.20-stable（目前确定可用）
- linux（确定可用的是CentOS和Ubuntu，其他的没测过）
- (option) Apache（改造未破坏ThinkPHP本身的功能，仍然可以用作Web服务）

## 安装

- [PHP](https://github.com/php/php-src)
- [Swoole扩展](https://github.com/swoole/swoole-src)

## 其它

- [ThinkPHP3.2.3](https://github.com/liu21st/thinkphp)
- 因为原生的ThinkPHP是面向Web开发的，需要进行一些改造才能适应常驻服务的特性，请使用我提供的ThinkPHP版本。

## 说明

DIServer是我在具体项目中，根据自身需求和一些遇到的问题整理出来的框架。

- 这个框架试图或者尝试解决以下问题：
- 一个便于设置和使用的网络服务框架，使得使用者可以更加专注的关注业务问题。
- 基于ThinkPHP提供的服务支持，使用户可以使用更简便的方法完成诸如数据库、缓存等常用服务的使用。

为了达到这个目的，我做了以下努力：

1. 快速建立一个或多个网络服务，各个服务可以同时监听一个或TCP\UDP的端口。
1. 建立一个一般性流程用于完成服务的配置、启动、重载、关闭等工作。
1. 一个一般性的流程用于管理、处理、反馈用户请求。
1. 可以通过配置文件控制服务的某些特性。
1. 提供一个默认的通讯协议和对应的解析方式，并允许用户根据自己需要重载协议。
1. 通过对ThinkPHP的修改，使之可以比较好的支持cli模式下，常驻服务的特性（如数据库的长连接）。
1. 可以与ThinkPHP本身Web功能共存，同一套配置（例如数据库配置）同时用于http服务和TCP\UDP服务。
1. 一些便利的管理脚本。
1. 以框架标准扩展的形式提供一些常用工具基础实现方案（如心跳帧），可根据业务需求自行改造。

## 样例

### Server的配置

```php
//还没写。。

```
#### 简单的服务开启方式
```php
vi YourServer.php

<?php
\define('DI_SERVER_NAME', 'YourServer');
//就酱，DIServer框架会自动创建目录
require_once '{$DIServerPath}\DIServer.php';
```

```shell
php YourServer.php
```

## 开源许可
Apache License Version 2.0http://www.apache.org/licenses/LICENSE-2.0.html
