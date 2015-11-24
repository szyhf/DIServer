#Server的业务日志文件拆分保存
#将拆分后的日志中的info行和error行单独提取保存
#本脚本应放在../APP_PATH/Shell目录下执行，否则不保证其可用性
#作者：Back 首发日期：20150917
#作者：Back 更新日期：20150923 
#               说明：  1、将非标准DILog日志另外提取出来
#                       2、将Warn级别的日志单独提取
#
appDirPath=$(cd `dirname $0`; pwd)"/../"
fileList=`ls $appDirPath|grep Server$`
for file in $fileList
do
        logFiles=`ls $appDirPath$file"/Log/"|grep Server.log$`
        for logFile in $logFiles
        do
                #获取日志的绝对路径
                logPath=`readlink -f $appDirPath$file"/Log/"$logFile`
                #echo $logPath
                #生成备份日志的绝对路径及文件名称
                bakLogPath="${logPath}."`date -d yesterday "+%Y%m%d"`
                cat $logPath > $bakLogPath
#                cat $bakLogPath |egrep "^\[[0-9]{4}-[01][0-9]-[0-3][0-9] [01][0-9]:[0-5][0-9]:[0-5][0-9]\]\[[i]\]" > $bakLogPath."info"
#                cat $bakLogPath |egrep "^\[[0-9]{4}-[01][0-9]-[0-3][0-9] [01][0-9]:[0-5][0-9]:[0-5][0-9]\]\[[e]\]" > $bakLogPath."error"
#                cat $bakLogPath |egrep "^\[[0-9]{4}-[01][0-9]-[0-3][0-9] [01][0-9]:[0-5][0-9]:[0-5][0-9]\]\[[w]\]" > $bakLogPath."warn"
#                cat $bakLogPath |egrep -v "^\[[0-9]{4}-[01][0-9]-[0-3][0-9] [01][0-9]:[0-5][0-9]:[0-5][0-9]\]\[[wie]{1}\]" > $bakLogPath."other"
                cat /dev/null > $logPath
        done
done
