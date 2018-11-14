<?php

include("/home/pi/scripts/config.php");
include("/home/pi/scripts/synced/common.php");

$event = "none";
if(isset($argv[1])){
	$event = $argv[1];
}

echo("Coms event: ".$event."\n");

if($event == "video_sync"){

	$codes = json_decode(file_get_contents('/tmp/codes.json'), true);
	$jresp = json_decode(file_get_contents('/tmp/https_response'), true);

	$RsyncRunning = false;
	exec("/bin/ps aux", $ps_out);
	$headers = array_shift($ps_out);
	foreach($ps_out AS $process){
		if(strpos($process, 'rsync') !== false){
			$RsyncRunning = true;
		}
	}
	if($RsyncRunning){
		log_message("rsync is allready running quiting");
		exit;
	}

	if(isset($jresp['exec'])){
		passthru($jresp['exec']);
	}

	if(file_exists('/tmp/sync_log')){
		unlink('/tmp/sync_log');
	}
	if(!file_exists('/media/videos/syncing_videos')){

		passthru('/usr/bin/sudo /bin/chmod -R 777 /media/videos/');
		mkdir('/media/videos/syncing_videos');
	}
	passthru('RSYNC_PASSWORD='.$codes['rsync_password'].' rsync --delete --recursive --log-file=/tmp/sync_log -dc --partial --progress "rsync://'.$codes['rsync_user'].'@'.$config['SERVER'].':'.$config['SRVPORT'].'/group_'.$jresp['group'].'" "/media/videos/syncing_videos"', $res);

	$rsync_log = file_get_contents('/tmp/sync_log');
	if(strpos($rsync_log, 'deleting') !== FALSE || strpos($rsync_log, '>') !== FALSE){
		echo("some files have been chnaged\n");
		passthru('/usr/bin/php /home/pi/scripts/synced/control.php chnanged_files');
	}else{
		echo("no changes in video playlist quiting\n");
	}
}elseif($event == "no_net" || $event == "faulty_json"){

	$file_list = array();
	exec("/usr/bin/find /tmp/usb/ -name '*.mp4' -size +2M", $file_list);

	$usbfirstmp4 = null;
	if(count($file_list) > 0){
		foreach($file_list AS $file){
			$usbfirstmp4 = $file;
		}
	}

	//did we find  mp4 on the usb disk
	if(isset($usbfirstmp4) && !empty($usbfirstmp4)){
		log_message("found mp4 on usb disk");
		$file_list = array();
		exec("/usr/bin/find /media/videos/syncing_videos -name '*.mp4' -size +2M", $file_list);
		$videosfirstmp4 = null;
		if(count($file_list) > 0){
			foreach($file_list AS $file){
				$videosfirstmp4 = $file;
			}
		}

		$replacefilesfromusb = false;

		if(isset($videosfirstmp4)){
			log_message("found video in playlist folder: $videosfirstmp4");
			$usbfile_info = stat($usbfirstmp4);
			$videofile_info = stat($videosfirstmp4);

			//is the usb file  a diffrent file
			if($videofile_info['size'] !=  $usbfile_info['size']){
				$replacefilesfromusb = true;
			}
		}else{//no files in videos
			$replacefilesfromusb = true;
		}

		if($replacefilesfromusb){
			log_message("removing old media");
			exec('/bin/rm -R /media/videos/syncing_videos/*');
			log_message("Moving video to playlist folder");
			mkdir('/media/videos/syncing_videos/1000');
			copy($usbfirstmp4, '/media/videos/syncing_videos/1000/usbmovie.mp4');
			log_message('copied mp4 file: '.$usbfirstmp4);
			
			passthru('/usr/bin/killall omxplayer.bin omxplayer');
		}
	}

}elseif($event == "pre_connect"){
	//Get Screen info from the hdmi connector
	exec('timeout -s 9 10 tvservice -d /tmp/upload/edid 2> /tmp/tvservice.err');

	//Check if the tvservice is down
	$error_msg = file_get_contents('/tmp/tvservice.err');
	if(strpos($error_msg, 'Failed to connect to TV service') !== FALSE){
        	//TV service is down, the player is most likly not playing content
	}
	$net_info =  get_standard_net_info();

	$data = array(
       		'local_ip' => $net_info['ip'],
	);

	file_put_contents('/tmp/upload/data.json', json_encode($data));

}elseif($event == "upgrade_done"){
	$BrowserRunning = false;
	exec("/bin/ps aux", $ps_out);
	$headers = array_shift($ps_out);
	foreach($ps_out AS $process){
		if(strpos($process, 'epiphany-browser') !== false){
			$BrowserRunning = true;
		}
	}

	//If the browser is running kill it then start it so we get a fresh view
	if($BrowserRunning){
		passthru('/usr/bin/killall xinit');
		passthru('/usr/bin/php /home/pi/scripts/synced/control.php');
	}
	/*  */
	if(md5_file('/home/pi/scripts/check_status.php') != '274d19789a721ef599a479e9a373d919'){
		echo("replacing check_status.php\n");
		replace_php_file('/home/pi/scripts/check_status.php', '/home/pi/scripts/synced/check_status.php');
	}
	 /* */
}
