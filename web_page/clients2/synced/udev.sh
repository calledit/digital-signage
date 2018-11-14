#!/bin/bash

export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
#[ `ps aux|grep '[b]ash /home/pi/scripts/synced/udev.sh'|wc -l` != 1 ] && wall not one

#if we cant find our pid in the first row of ps ax quit
OWNPID=$BASHPID
ps ax|grep '[b]ash /home/pi/scripts/synced/udev.sh'|head -n 1|grep "$OWNPID" ||exit

#if we have not started properly do not run anything
[ "$(runlevel)" != "N 3" ] && exit

#env > /tmp/env
#TYPE=$SUBSYSTEM
#if [ "$ACTION" == "add" ]
#then
#	wall NEW USB device
#else
#	wall REMOVED USB device
#fi

wall USB device trigger

sleep 2
nohup /home/pi/scripts/synced/cron.sh &

#Hold the next action
if [ "$ACTION" == "remove" ]
then
	sleep 10
fi
