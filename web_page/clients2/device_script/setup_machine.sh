#!/bin/bash

SSID=""
PSK=""

ls /media/us*/media_config.conf 2> /dev/null
ConfInserted=$?
if [ "${ConfInserted}" == "0" ]
then
	file /media/us*/media_config.conf |grep 'shell script'
	IsShell=$?
	if [ "$IsShell" == "0" ]
	then
		. /media/us*/media_config.conf
	fi
else
	#no config file found
	exit 1
fi

echo $SSID


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
