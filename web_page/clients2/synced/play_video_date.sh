#!/bin/bash

play_dir=$2

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
		echo Search for non loop craches

		#check the log file for a crash
		if false
		then
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
		fi
	fi



	#if it has crached restart it
	if [ "${NeedReboot}" == "1" ]
	then
		/home/pi/scripts/synced/play_video_date.sh play $play_dir
	else
		#empty the log file
		grep -a 'DEBUG: OMXReader::SeekTime(0)' /tmp/omxplayercwd/omxplayer.log > /tmp/omxplayercwd/loop.txt
		echo > /tmp/omxplayercwd/omxplayer.log
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

	nohup omxplayer --no-osd --genlog --loop -b /media/videos/syncing_videos/${play_dir}/* >/dev/null &
fi
