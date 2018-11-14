#!/bin/bash

SERVER=SERVERDNSNAME
SRVPORT=80
SecretKey=SALTINGKEY123456

#Get the device serial we will use it as a uniq identifier
DeviceSerial=`grep 'Serial' /proc/cpuinfo |egrep -o '[^ ]+$'`

#Get the ethernet address
DeviceEthernet=`cat /sys/class/net/eth0/address`

#Generate a uniq id from the ethernet address and serial number
DeviceIdentifier=`echo "${DeviceSerial}_${DeviceEthernet}"|sha256sum|egrep -o '^[^ ]+'`

#Generate a auth code from the Secret and our device Identifier
AuthCode=`echo "${DeviceIdentifier}_${SecretKey}"|sha256sum|egrep -o '^[^ ]+'`

echo downloading new version
#Download new get videos version
curl -fsSo /tmp/get_videos.tmp "https://${SERVER}/index.php?player_id=${DeviceIdentifier}&authcode=${AuthCode}&upgrade=get_videos"
GotNewVersion=$?
if [ "${GotNewVersion}" != "0" ]
then
	echo "Could not download new get_videos version" 1>&2
	exit 1
fi

#Download new play videos version
curl -fsSo /tmp/play_videos.tmp "https://${SERVER}/index.php?player_id=${DeviceIdentifier}&authcode=${AuthCode}&upgrade=play_videos"
GotNewVersion=$?
if [ "${GotNewVersion}" != "0" ]
then
	echo "Could not download new play_videos version" 1>&2
	exit 1
fi

Oldhash=`cat /home/pi/play_videos.sh|sha256sum|egrep -o '^[^ ]+'`
Newhash=`cat /tmp/play_videos.tmp|sha256sum|egrep -o '^[^ ]+'`
if [ "${Oldhash}" != "${Newhash}" ]
then
	echo new play_videos
	chmod 700 /tmp/play_videos.tmp
	file /tmp/play_videos.tmp |grep 'shell script' && mv /tmp/play_videos.tmp /home/pi/play_videos.sh || echo failed to upgrade play_videos
fi

Oldhash=`cat /home/pi/get_videos.sh|sha256sum|egrep -o '^[^ ]+'`
Newhash=`cat /tmp/get_videos.tmp|sha256sum|egrep -o '^[^ ]+'`
if [ "${Oldhash}" != "${Newhash}" ]
then
	echo new get_videos
	chmod 700 /tmp/get_videos.tmp
	file /tmp/get_videos.tmp |grep 'shell script' && mv /tmp/get_videos.tmp /home/pi/get_videos.sh || echo failed to upgrade get_videos
fi

rsync --delete -rc --partial --progress "rsync://${SERVER}:${SRVPORT}/misc" "/home/pi/misc"

exit 0
