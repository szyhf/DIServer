#!/bin/sh
#定期将日志文件拆分保存
#日志目录
logDirPath=$(cd `dirname $0`; pwd)"/../Log"
#当前日志路径
logPath=$(cd `dirname $0`; pwd)"/../Log/WGServer.php.log"
oldLog=${logDirPath}"/WGServer."`date -d yesterday "+%Y%m%d"`".php.log"
#echo $oldLog
#echo $logPath
cp $logPath $oldLog
cat /dev/null > $logPath