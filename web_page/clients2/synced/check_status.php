<?php
chdir("/home/pi/scripts");

if(isset($argv[1]) && $argv[1] == 'cron'){
	echo("Sleep for some ammount of time so that all devices don't connect at the exact same time\n");
	sleep(rand(0,120));
}

//Get config variables
include "config.php";

//Generate and save auth codes
$codes = array();
$codes['device_identifier'] = get_device_identifier();
$codes['auth_code'] = get_device_authcode($codes['device_identifier'], $config['SecretKey']);
$codes['rsync_password'] = get_rsync_auth($codes['auth_code'], 'password');
$codes['rsync_user'] = get_rsync_auth($codes['device_identifier'], 'user');

$file_cont = "";
if(file_exists('/tmp/codes.json')){
	$file_cont = file_get_contents('/tmp/codes.json');
}
$json_codes = json_encode($codes);
if($json_codes != $file_cont){
	file_put_contents('/tmp/codes.json', $json_codes);
}

$version = get_version('synced');

//Create upload directory
if(!file_exists('/tmp/upload')){
	mkdir('/tmp/upload');
}

//Do a pre connect coms event
passthru("/usr/bin/timeout 300 /usr/bin/php synced/coms.php pre_connect");

$curl_files = array();
$files_to_upload = array_diff(scandir('/tmp/upload'), array('..', '.'));
foreach($files_to_upload AS $file_name){
	$full_name = '/tmp/upload/'.$file_name;
	$curl_files[] = '-F "'.$file_name.'=@'.$full_name.'"';
}


if(file_exists('/tmp/https_response')){
	unlink('/tmp/https_response');
}

exec('curl '.implode(' ', $curl_files).' -fsSo /tmp/https_response "https://'.$config['SERVER'].'/index.php?player_id='.$codes['device_identifier'].'&authcode='.$codes['auth_code'].'&version='.$version.'&apiv=2"');
if(!file_exists('/tmp/https_response')){
	coms_event("no_net");
	echo("No response file asuming network error");
	exit(1);
}
$resonse = file_get_contents('/tmp/https_response');

$jresp = json_decode($resonse, true);
if($jresp == FALSE){
	coms_event("faulty_json");
	echo("Faulty or no json response");
	exit(1);
}

if(isset($jresp['upgrade'])){
	echo "Start upgrading\n";

	$scanned_directory = array_diff(scandir('synced'), array('..', '.'));
	foreach($scanned_directory AS $file_name){
		unlink('synced/'.$file_name);
	}

	foreach($jresp['upgrade'] AS $file_name){
		exec('curl -fsSo synced/'.$file_name.' "https://'.$config['SERVER'].'/index.php?player_id='.$codes['device_identifier'].'&authcode='.$codes['auth_code'].'&apiv=2&upgrade='.$file_name.'"');
	}
	exec("/bin/chmod 755 synced/*");

	coms_event("upgrade_done");
}

coms_event("video_sync");

