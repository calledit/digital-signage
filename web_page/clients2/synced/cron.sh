#!/bin/bash

cd /home/pi/scripts/synced


if [ "$1" == "udev_add" ]
then
	/usr/bin/wall add
	exit 0
else
	if [ "$1" == "udev_remove" ]
	then
		/usr/bin/wall remove
		exit 0
	fi
fi

#if [ "$1" == "reboot" ]
#then
#	echo startx -- -nocursor
#fi
/usr/bin/php control.php

#./play_videos.sh check

