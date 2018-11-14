#!/bin/bash

if [ "$1" == "check" ]
then
	echo check that the player is running
	NeedReboot=0

	ps aux |egrep '[o]mxplayer'
	OmxRunning=$?

	#if not running start it
	if [ "${OmxRunning}" != "0" ]
	then
		NeedReboot=1
	fi

	#check the log file for a crash
	grep -A 1 OMX_IndexConfigTimeCurrentVideoReference /tmp/omxplayercwd/omxplayer.log |tail -n 2 > /tmp/log_final2.txt

	grep OMX_IndexConfigTimeCurrentVideoReference /tmp/log_final2.txt
	OmxCrached=$?
	#Could it have crashed
	if [ "${OmxCrached}" == "0" ]
	then
		grep OMXThread /tmp/log_final2.txt
		OmxCrached=$?

		#if it has crached restart it
		if [ "${OmxCrached}" != "0" ]
		then
			NeedReboot=1
		fi
	fi




	#if it has crached restart it
	if [ "${NeedReboot}" == "1" ]
	then
		/home/pi/play_videos.sh
	fi
else
	#Create log dir
	mkdir /tmp/omxplayercwd 2>/dev/null

	#go to log dir
	cd /tmp/omxplayercwd

	#Kill previus omxplayer
	killall omxplayer.bin omxplayer

	#Delete old log files
	rm /tmp/omxplayercwd/*

	#sudo killall fbi
	VideoCount=`ls -l /home/pi/videos/ | wc -l`

	play_dir="non"
	ls /home/pi/videos/|sort > /tmp/omxplayercwd/vidlist.txt
	cur_time=`date '+%s'`
	while read directory
	do
		play_date=`echo $directory | egrep -o '[0-9]+'`
		if [ "$cur_time" -ge "$play_date" ]
		then
			play_dir="$play_date"
		fi
	done < /tmp/omxplayercwd/vidlist.txt

	if [ "${VideoCount}" == "1" ] || [ "${play_dir}" == "non" ]
	then
		echo no videos playing no videos file
		#sudo fbi -T 2 --noverbose /home/pi/misc/no_video.jpg
		nohup omxplayer --genlog --no-osd --loop -b /home/pi/misc/no_video.mp4 >/dev/null &
		exit 1
	fi

	nohup omxplayer --no-osd --genlog --loop -b /home/pi/videos/${play_dir}/* >/dev/null &
fi
