#!/bin/bash

#Get current date
cur_time=`date '+%s'`



#get the sheduled folder to play
play_dir="non"
ls /home/pi/videos/|sort > /tmp/vidlist.txt
while read directory
do
	play_date=`echo $directory | egrep -o '[0-9]+'`
	if [ "$cur_time" -ge "$play_date" ]
	then
		play_dir="$play_date"
	fi
done < /tmp/vidlist.txt



if [ "$1" == "check" ]
then
	echo check that the player is running
	NeedReboot=0

	ps aux |egrep '[o]mxplayer' > /dev/null
	OmxRunning=$?

	#if not running start it
	if [ "${OmxRunning}" != "0" ]
	then
		NeedReboot=1
	else
		#if it is running see if we are sopose to play another video and the date has roled over

		cur_play_date=`ps aux|grep [o]mxplayer.bin|egrep -o 'videos/[0-9]+'|egrep -o '[0-9]+'`
		if [ "$cur_play_date" == "" ]
		then
			 cur_play_date=0
		fi
		if [ "${play_dir}" != "non" ]
		then
			echo cur_play_date: $cur_play_date play_dir: $play_dir
			if [ "$play_dir" -gt "$cur_play_date" ]
			then
				NeedReboot=1
			fi
		fi
	fi

	#check the log file for a crash
	grep -A 1 OMX_IndexConfigTimeCurrentVideoReference /tmp/omxplayercwd/omxplayer.log |tail -n 2 > /tmp/log_final2.txt

	echo empty > /tmp/omxplayercwd/omxplayer.log

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

	if [ "${play_dir}" == "non" ]
	then
		echo no videos playing no videos file
		#sudo fbi -T 2 --noverbose /home/pi/misc/no_video.jpg
		nohup omxplayer --genlog --no-osd --loop -b /home/pi/misc/no_video.mp4 >/dev/null &
		exit 1
	fi

	nohup omxplayer --no-osd --genlog --loop -b /home/pi/videos/${play_dir}/* >/dev/null &
fi
