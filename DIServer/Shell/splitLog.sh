#!/bin/sh
#定期将DIServer的业务日志文件拆分保存
#将拆分后的日志中的info行和error行单独提取保存
#本脚本应放在../APP_PATH/Shell目录下执行，否则不保证其可用性
#作者：Back 首发日期：20150917
#
appDirPath=$(cd `dirname $0`; pwd)"/../"
fileList=`ls $appDirPath|grep Server$`
for file in $fileList
do
	logFiles=`ls $appDirPath$file"/Log/"|grep Server.log$`
	for logFile in $logFiles
	do
		#获取日志的绝对路径
		logDirPath=`readlink -f $appDirPath$file"/Log/"`"/"
		logFilePath="${logDirPath}${logFile}"
		#echo $logFilePath
		#生成备份日志的绝对路径及文件名称
		bakLogFilePath="${logFilePath}."`date -d yesterday "+%Y%m%d"`
		cat $logFilePath > $bakLogFilePath
#		cat $bakLogFilePath |egrep "^\[[0-9]{4}-[01][0-9]-[0-3][0-9] [01][0-9]:[0-5][0-9]:[0-5][0-9]\]\[[i]\]" > \
#			"${logDirPath}/error/${logFile}."`date -d yesterday "+%Y%m%d"`".error"
#		cat $bakLogFilePath |egrep "^\[[0-9]{4}-[01][0-9]-[0-3][0-9] [01][0-9]:[0-5][0-9]:[0-5][0-9]\]\[[e]\]" > \
 #                       "${logDirPath}/error/${logFile}."`date -d yesterday "+%Y%m%d"`".info"
#		cat $bakLogFilePath |egrep "^\[[0-9]{4}-[01][0-9]-[0-3][0-9] [01][0-9]:[0-5][0-9]:[0-5][0-9]\]\[[w]\]" > \
 #                       "${logDirPath}/error/${logFile}."`date -d yesterday "+%Y%m%d"`".info"
		cat /dev/null > $logFilePath
	done
done
