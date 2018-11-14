<?php

include("/home/pi/scripts/synced/common.php");

$com = '';
if(isset($argv[1])){
	$com = $argv[1];
}

//Create upload directory
if(!file_exists('/tmp/upload')){
	mkdir('/tmp/upload');
}

//Get Avalible Video Play dates
$avalible_dates = get_play_dates();

//Get the folder that we should be playing from
$folder_to_play = get_what_should_be_played($avalible_dates);

//Get running processes
$BrowserRunning = false;
$OMXRunning = false;
$CurrentPlayingFile = false;


exec("/bin/ps aux", $ps_out);
$headers = array_shift($ps_out);
foreach($ps_out AS $process){
	if(strpos($process, 'epiphany-browser') !== false){
		$BrowserRunning = true;
	}else if(strpos($process, 'omxplayer.bin') !== false){
		$OMXRunning = true;
		$CurrentPlayingFile = explode($sync_dir.'/', $process);
		$CurrentPlayingFile = explode('/', $CurrentPlayingFile[1]);
		$CurrentPlayingFile = $CurrentPlayingFile[0];
	}
}

$show_browser = false;
$reload_the_player = false;
//If there is no videos show the browser so we can tell the user that there is no videos
if($folder_to_play === false){
	$show_browser = true;
}else{

	//If the player is not playing the file it should be playing
	if($CurrentPlayingFile != $folder_to_play){
		$reload_the_player = true;
	}elseif($com == 'chnanged_files'){
		$reload_the_player = true;
	}
	if(play_file_is_url($folder_to_play) !== false){
		$show_browser = true;
	}
}

$show_prompt = false;
//Show the browser if we have a keyboard attached
if(is_keyboard_connected()){
	$show_browser = true;
	
	//If a keyboard and a usb srive is connected show the prompt
	if(is_usb_drive_connected()){
		$show_prompt = true;
	}
}


$screenshot = pi_take_screenshot();
if(!$screenshot){
	log_message("Screenshot fail, rebooting");
	passthru('/usr/bin/sudo /sbin/reboot');
}

if($show_browser){
	if(!$BrowserRunning){
		if(!$show_prompt){
			log_message('starting webbrowser');
		}
		passthru('/usr/bin/killall omxplayer.bin omxplayer xinit');
		if(!$show_prompt){//dont start x if we are to show the prompt
			passthru('/usr/bin/startx -- -nocursor');
		}
	}else{
		if($show_prompt){//if the browser is running and we are to show the prompt kill x
			log_message('showing prompt');
			passthru('/usr/bin/killall omxplayer.bin omxplayer xinit');
		}
	}
}else{
	if($BrowserRunning){
		log_message('starting omxplayer');
		passthru('/usr/bin/killall xinit');
		passthru('/usr/bin/killall omxplayer.bin omxplayer');
		passthru('/home/pi/scripts/synced/play_video_date.sh check '.$folder_to_play);
	}else{
		if($reload_the_player){
			log_message('reloading omxplayer');
			passthru('/home/pi/scripts/synced/play_video_date.sh play '.$folder_to_play);
		}else{
			log_message('checking status of omxplayer');
			passthru('/home/pi/scripts/synced/play_video_date.sh check '.$folder_to_play);
		}
	}
}



