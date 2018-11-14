#!/bin/bash

#Make sure the wlan is on
ifconfig wlan0
HasWlan=$?
if [ "$HasWlan" == "0" ] 
then
	if ifconfig wlan0 | grep 'inet addr' > /dev/null
	then
		echo we have wlan
	else
		sudo ifup wlan0
		sudo ifdown wlan0
		sudo ifup wlan0
	fi
fi

ls /media/us*/media_config.conf 2> /dev/null
ConfInserted=$?
if [ "${ConfInserted}" == "0" ]
then
	SSID=""
	PSK=""

	file /media/us*/media_config.conf |grep 'shell script' 2> /dev/null
	IsShell=$?
	if [ "$IsShell" == "0" ]
	then
		#Source config vars
		. /media/us*/media_config.conf


		#Scan for network
		#sudo iwlist wlan0 scan



		#Create wifi config file
		echo "ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev" > wpa_supplicant.conf
		echo "update_config=1" >> wpa_supplicant.conf

		echo "network={" >> wpa_supplicant.conf
		echo "ssid=\"${SSID}\"" >> wpa_supplicant.conf
		echo "psk=\"${PSK}\"" >> wpa_supplicant.conf
		echo "}" >> wpa_supplicant.conf

		chmod 600 wpa_supplicant.conf
		sudo chown root:root wpa_supplicant.conf


		#move wifi config file
		sudo mv wpa_supplicant.conf /etc/wpa_supplicant/wpa_supplicant.conf

		#Reset wifi connection
		sudo ifdown wlan0
		sudo ifup wlan0
		sleep 15
	fi
fi

echo script initialized
if [ "$1" == "cron" ]
then
	echo waiting a random time under 2 minutes
	#Sleep for some ammount of time so that all devices don't connect at the exact same time
	sleep $(( ( RANDOM % 120 )  + 1 ))
fi


SERVER=SERVERDNSNAMR
SRVPORT=80
SecretKey=SALTINGKEY234567
VERSION=20

ps aux |egrep '[r]sync' >/dev/null
RsyncRunning=$?
if [ "${RsyncRunning}" == "0" ]
then
	echo There is a diffrent instance allredy running quiting
	exit
fi
echo Gathering Info

#Get the device serial we will use it as a uniq identifier
DeviceSerial=`grep 'Serial' /proc/cpuinfo |egrep -o '[^ ]+$'`

#Get the ethernet address
DeviceEthernet=`cat /sys/class/net/eth0/address`

#Generate a uniq id from the ethernet address and serial number
DeviceIdentifier=`echo "${DeviceSerial}_${DeviceEthernet}"|sha256sum|egrep -o '^[^ ]+'`

#Generate a auth code from the Secret and our device Identifier
AuthCode=`echo "${DeviceIdentifier}_${SecretKey}"|sha256sum|egrep -o '^[^ ]+'`

Rsync_User=`echo "user_${DeviceIdentifier}"|sha1sum|egrep -o '^[^ ]+'`
export RSYNC_PASSWORD=`echo "password_${AuthCode}"|sha1sum|egrep -o '^[^ ]+'`

echo Gathering tv info

#Get Screen info from the hdmi connector
tvservice -d /tmp/tv_info.dat

#make sure the file exists
touch /tmp/tv_info.dat

echo conntacting server over https ansking for group membership
#Submit the Device Serial to teh master server and get a sync key back
curl -F "edid=@/tmp/tv_info.dat" -fsSo /tmp/bt_sync_key "https://${SERVER}/index.php?player_id=${DeviceIdentifier}&authcode=${AuthCode}&version=${VERSION}"
GotKey=$?

#If we got a valid response from our master server
if [ "${GotKey}" == "0" ]
then
	echo we got answer from https server
	SyncKey=`cat /tmp/bt_sync_key`
	rm /tmp/bt_sync_key

	#If the server have a new version of the software for us
	if [ "${SyncKey}" == "NEW_VERSION_AVALIBLE" ]
	then
		echo new version avalible
		/home/pi/upgrade_software.sh
		exit 0
	fi


	#Delete the log
	echo > /tmp/sync_log
	echo Start rsync video syncing
	rsync --delete --recursive --log-file=/tmp/sync_log -dc --partial --progress "rsync://${Rsync_User}@${SERVER}:${SRVPORT}/group_${SyncKey}" "/home/pi/syncing_videos"
	egrep '>|deleting' /tmp/sync_log > /dev/null
	FileTransferd=$?
	if [ "${FileTransferd}" == "0" ]
	then
		echo some files have been chnaged
		rm /home/pi/videos/*
		VideoCount=`ls -l /home/pi/syncing_videos/ | wc -l`
		if [ "${VideoCount}" != "1" ]
		then
			ln -s /home/pi/syncing_videos/* /home/pi/videos/.
		fi
		/home/pi/play_videos.sh
	else
		echo no changes in video playlist quiting
	fi
else
	echo https server did not answer nicly quiting
fi
