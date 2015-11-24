#!/bin/sh
#根据Server目录的名字，搜索正在运行的Server（未重命名）
#找到后，向Server的Master进程发送kill-10信号，达到热重载的目的
#作者：Back 首发日期：20150917
#作者：Back 更新日期：20150922 内容：允许使用形如“reload.sh HT”的命令热重载指定的服务，如果不输入则重载所有服务
#
appDirPath=$(cd `dirname $0`; pwd)"/../"
fileList=`ls $appDirPath|grep ${1}Server$`
for file in $fileList
do
	#目录名以Server结尾的，即需要关注的进程名${ServerName}.php
	serverName="${file}.php"
	psID=$(pstree -ap |grep "^  |-php,[0-9]* ${serverName}"|awk -F , '{print $2}'|awk '{print $1}')
	echo $file": kill -10 "$psID
	kill -10 $psID
done
