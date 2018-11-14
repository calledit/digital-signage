<?php
$cur_time = time();
$sync_dir = '/media/videos/syncing_videos';
$wifi_cred_file = '/etc/wpa_supplicant/wpa_supplicant.conf';

function play_file_is_url($folder_to_play){
	global $sync_dir;
	$date_folder = $sync_dir.'/'.$folder_to_play.'/';
	$files = array_diff(scandir($date_folder), array('..', '.'));
	$linkFile = false;
	foreach($files AS $play_file){
		if(substr($play_file,-4) == '.url'){
			$linkFile = $date_folder.$play_file;
		}
	}
	if($linkFile && file_exists($linkFile)){
		return(file_get_contents($linkFile));
	}
	return(false);
}

function get_tv_status_info(){
	exec('/usr/bin/timeout -s 9 10 tvservice -d /tmp/upload/edid 2> /tmp/tvservice.err');
	
	//Check if the tvservice is down
	$error_msg = file_get_contents('/tmp/tvservice.err');
	if(strpos($error_msg, 'Failed to connect to TV service') !== FALSE){
		return false;
	}
	return true;
}

function pi_restart(){
	passthru('/usr/bin/sudo /sbin/reboot');
}

function pi_take_screenshot(){
	exec('/usr/bin/sudo /usr/bin/timeout -s 9 20 /home/pi/scripts/synced/raspi2png --width 160 --pngname /tmp/upload/screen.png', $out, $res);
	
	if($res != 0){
		return false;
	}
	return true;
}

function get_play_dates(){
	global $sync_dir;
	$dates = array();
	if(file_exists($sync_dir)){
		$dates = array_diff(scandir($sync_dir), array('..', '.'));
	}
	sort($dates);
	return($dates);
}

function log_message($message){
	//log to the normal php log
	error_log($message, 0);
	
	//Then log to the main console so we can see it if on the screen if omx or the browser is not running
	passthru('echo '.escapeshellarg($message).' | /usr/bin/sudo /usr/bin/tee -a /dev/console');
}

function validate_php_file($file_name){
	exec('/usr/bin/php -l '.$file_name, $out, $result);
	if($result == 0){
		return true;
	}
	return false;
}

function replace_php_file($file_to_replace, $new_file){

	//Only replace file if both the new and old are valid php
	if(validate_php_file($file_to_replace) && validate_php_file($new_file)){
		$old_file_c = file_get_contents($file_to_replace);
		$new_file_c = file_get_contents($new_file);

		//fill the old file with data from the new one so we dont replace the permisions
		file_put_contents($file_to_replace, $new_file_c);

		//if the file is not valid after the move we restore the original
		if(!validate_php_file($file_to_replace)){
			file_put_contents($file_to_replace, $old_file_c);
		}
	}
}


function is_powersupply_ok(){
	return false;//HACK this function does not work anyway
	if(file_exists('/tmp/powersupply_fail')){
		return false;
	}
	//Loop to cause cpu stress
	for($i=0;$i<5000000;$i++){
	}
	//then Read the undervoltage indicator pin
	exec('gpio -g read 35', $ps_out);
	$voltage_ok = $ps_out[0];
	if($voltage_ok == "1"){
		return true;
	}
	file_put_contents('/tmp/powersupply_fail', $voltage_ok);
	return false;
}

function read_wifi_cred(){
	global $wifi_cred_file;
	$res = array(
		'ssid' => '',
		'psk' => '',
	);
	exec('/usr/bin/sudo /bin/cat '.$wifi_cred_file, $ps_out);
	foreach($ps_out AS $scan_line){
		if(strpos($scan_line, 'ssid="') !== false){
				$res['ssid'] = substr(strstr($scan_line, 'ssid="'), 6, -1);
		}elseif(strpos($scan_line, 'psk="') !== false){
				$res['psk'] = substr(strstr($scan_line, 'psk="'), 5, -1);
		}
	}
	return $res;
}

function write_wifi_cred($cred){
	global $wifi_cred_file;
	$cont = "ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev\n";
	$cont .= "update_config=1\n";
	if($cred['ssid'] != "" && $cred['psk'] != ""){
		$cont .= "network={\n";
		$cont .= "ssid=\"".$cred['ssid']."\"\n";
		$cont .= "psk=\"".$cred['psk']."\"\n";
		$cont .= "}";
	}
	file_put_contents("/tmp/wpa_supplicant.conf", $cont);
	passthru('/usr/bin/sudo /bin/chown pi:pi '.$wifi_cred_file);
	file_put_contents($wifi_cred_file, $cont);
	passthru('/usr/bin/sudo /bin/chown root:root '.$wifi_cred_file);
	//passthru('/usr/bin/sudo /bin/chown root:root /tmp/wpa_supplicant.conf && /usr/bin/sudo /bin/chmod 600 /tmp/wpa_supplicant.conf && /usr/bin/sudo /bin/mv -f /tmp/wpa_supplicant.conf '.$wifi_cred_file);
}

function get_what_should_be_played($avalible_dates){
	global $cur_time;
	$folder_to_play = false;
	foreach($avalible_dates AS $play_from_date){
		if($play_from_date < $cur_time){
			$folder_to_play = $play_from_date;
		}
	}
	return($folder_to_play);
}
function reboot_network_interface($net_interface){
	passthru('/usr/bin/sudo /sbin/ifdown '.$net_interface);
	passthru('/usr/bin/sudo /sbin/ifup '.$net_interface);

	sleep(1);
	exec('/sbin/wpa_cli '.$net_interface, $ps_out);
	foreach($ps_out AS $scan_line){
		$line_vals = explode('=', $scan_line);
		if($line_vals[0] == 'wpa_state'){
			if($line_vals[1] == 'INTERFACE_DISABLED'){
				passthru('/usr/bin/sudo /sbin/ifconfig '.$net_interface.' up');
			}
		}
	}
}

function get_standard_net_info(){
       	exec('ip route get 8.8.8.8', $Ip);
       	$Ip = array_shift($Ip);
       	$Ip = explode('src ', $Ip);
       	$Ip = array_pop($Ip);
       	return array('ip' => $Ip);
}

function get_network_interfaces(){
	//Get Network config
	$net_interfaces = array_diff(scandir('/sys/class/net'), array('..', '.'));
	return($net_interfaces);
}
function get_network_interface_info($net_interface){
	$ip_config = array();
	$networks = array();
	exec('/sbin/ifconfig '.$net_interface, $ps_out);
	foreach($ps_out AS $scan_line){
		if(strpos($scan_line, 'inet addr:') !== false){
			$ip_info = explode('  ', $scan_line);
			foreach($ip_info AS $space_part){
				$info_data = explode(':', $space_part);
				if(count($info_data) == 2){
					$ip_config[$info_data[0]] = $info_data[1];
				}
			}
		}
	}
	return($ip_config);
}
function get_wlan_interface($net_interfaces){
	foreach($net_interfaces AS $interface_name){
		if(strpos($interface_name, 'wlan') !== false){
			return($interface_name);
		}
	}
	return(false);
}

function is_keyboard_connected(){
	$hid_devices = array_diff(scandir('/sys/bus/hid/devices'), array('..', '.'));
	return(count($hid_devices) > 0);
}

function is_usb_drive_connected(){
        $partitions = array_diff(scandir('/dev/disk/by-id'), array('..', '.'));
	$usb_partitions = array();
	foreach($partitions AS $partition){
		if(strpos($partition, 'usb-') === 0){
			$usb_partitions[] = $partition;
		}
	}
	return(count($usb_partitions) > 0);
}

function get_wlan_networks($wlan_interface){
	$networks = array();
	exec('/usr/bin/sudo /sbin/iwlist '.$wlan_interface.' scan', $ps_out);
	foreach($ps_out AS $scan_line){
		if(strpos($scan_line, 'ESSID') !== false){
			$net_name = strstr($scan_line,'"');
			$net_name = substr($net_name, 1, -1);
			if($net_name != ""){
				$networks[] = $net_name;
			}
		}
	}
	return(array_unique($networks));
}
