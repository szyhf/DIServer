DIServer Framework
=======================
这个框架还在实现中，但有些东西发生了改变。

## 简介

+ 一个由swoole_server提供网络服务的TCP/UDP网络服务框架（勉强算是个全栈框架）。
+ 刚开始是打算嵌入ThinkPHP的，但最后还是决定自己实现。
+ ORM部分打算使用TP的内部实现。

## 特性

- 使用了swoole扩展实现网络通讯。
- 提供包括IOC、Session、Config、Cache、Task、Middleware、ORM、Tick、Process等常见的工具的接口及参考实现（可以自己扩展或者使用composer）。
- 提供完整的即插即用容器服务和运作架构。
- 包括test、start、stop、reload、status、restart、kill在内的控制命令。

## 环境

- php5.6+ 
- Swoole1.8.0-stable（目前确定可用）
- linux（确定可用的是CentOS和Ubuntu，其他的没测过）
- (option) Apache（改造未破坏ThinkPHP本身的功能，仍然可以用作Web服务）

## 安装

- [PHP](https://github.com/php/php-src)
- [Swoole扩展](https://github.com/swoole/swoole-src)

## 其它

- [DICrontab](https://github.com/szyhf/DICrontab)  一个支持Linux Crontab语法的日程表工具类库，为DIServer组件之一，可以独立工作，用于实现定时日程安排，精确到秒，基于swool_timmer_after实现。


## 说明

DIServer是我在具体项目中，根据自身需求和一些遇到的问题整理出来的框架。

- 这个框架试图或者尝试解决以下问题：
- 一个便于设置和使用的网络服务框架，使得使用者可以更加专注的关注业务问题。

为了达到这个目的，我做了以下努力：

1. 快速建立一个或多个网络服务，各个服务可以同时监听一个或TCP\UDP的端口。
1. 建立一个一般性流程用于完成服务的配置、启动、重载、关闭等工作。
1. 一个一般性的流程用于管理、处理、反馈用户请求。
1. 可以通过配置文件控制服务的某些特性。
1. 提供一个默认的通讯协议和对应的解析方式，并允许用户根据自己需要重载协议。
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
include _PATH_TO_DISERVER_;
```
##### 在Shell中执行
```shell
php YourServer.php start|stop|restart|reload|kill|status
```

## 开源许可
Apache License Version 2.0http://www.apache.org/licenses/LICENSE-2.0.html
