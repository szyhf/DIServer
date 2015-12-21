#!/bin/bash
#根据Server的名字，强制终止这个Server的所有进程
#用于快速处理Server严重阻塞无法启动的情况
#作者：Back
#时间：2015.09.22
#
#根据参数获得要终止的Server的名字
while getopts "s:t:" arg
do
	case $arg in 
	s)
	#因为kill -9是毁灭性的行为，所以必须输入s参数指名ServerName，否则不处理。
		echo "Name of server is :${OPTARG}" #参数存在$OPTARG中
		serverName="${OPTARG}"
		#
		serverPIDs=`ps -x|egrep "${serverName}Server.php$"|awk '{print $1}'`
		for pid in $serverPIDs
		do
			kill -9 $pid
		    echo $pid" of ${serverName} is killed."
		done
		exit 0
	;;
	t)
		#测试参数，根据t的Server显示将被干掉的Server的名称和PID
		echo "Name of server is :${OPTARG}" #参数存在$OPTARG中
        serverName="${OPTARG}"
        serverPIDs=`ps -x|egrep "${serverName}Server.php$"|awk '{print $1}'`
        for pid in $serverPIDs
        do
	        echo $pid" of ${serverName} will kill."
        done
        exit 0
	;;
	?) #当有不认识的选项的时候arg为?
		echo "unkonw argument."
		exit 1
	;;
	esac
done
echo "因为kill -9是毁灭性的行为，所以必须输入-s参数指名ServerName，否则不处理。"
echo "也可以使用-t参数进行测试，只会显示可能被kill的进程id，不会执行kill-9命令。"
