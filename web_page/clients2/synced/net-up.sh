#!/bin/bash

export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

#if we cant find our pid in the first row of ps ax quit
OWNPID=$BASHPID
ps ax|grep '[b]ash /home/pi/scripts/synced/net-up.sh'|head -n 1|grep "$OWNPID" ||exit

#if we have not started properly wait 20 seconds
[ "$(runlevel)" != "N 3" ] && sleep 20

#env > /tmp/env
#TYPE=$SUBSYSTEM
#if [ "$ACTION" == "add" ]
#then
#	wall NEW USB device
#else
#	wall REMOVED USB device
#fi

wall network conneted trigger

sleep 2
nohup /usr/bin/php /home/pi/scripts/check_status.php &

#Hold the next action
if [ "$ACTION" == "remove" ]
then
	sleep 10
fi
