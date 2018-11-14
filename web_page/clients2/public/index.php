<?php
//echo "dfsdfsdf:s sfd";
ini_set('display_errors', 1);
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/clients');

function xss_clean(&$arr_2_clean){
	foreach ($arr_2_clean as $nri => $v) {
		if(is_array($arr_2_clean[$nri]))
			xss_clean($arr_2_clean[$nri]);
		else
			$arr_2_clean[$nri] = htmlspecialchars($v);
	}
}

/*clean most xss Things*/
xss_clean($_GET);
xss_clean($_POST);


require_once('config.php');
$synced_script_folder = '/var/www/clients/synced';

if(isset($_GET['player_id']) && isset($_GET['authcode'])){
	$player_id = strval($_GET['player_id']);
	$authcode = strval($_GET['authcode']);
	$APIV = 1;
	$json_resp = array();
	if(isset($_GET['apiv'])){
		$APIV = intval($_GET['apiv']);
	}

	//Verify that the device is has the Secret key
	$AcctualAuthCode = hash('sha256', $player_id.'_'.$SecretKey."\n");
	if($AcctualAuthCode == $authcode){

		//Find the player entry in the database
		$MatchingPlayers = Player::GetListByProperty(array('hardwareid' => $player_id));
		$NrRecivedPlayers = count($MatchingPlayers);
		if($NrRecivedPlayers == 1){
			$player = array_pop($MatchingPlayers);
		}else if($NrRecivedPlayers > 1){
			throw new Exception("Got duplicate player with the same hardwareid");
		}
		
		//If we have extra data
		$extra_data = array();
		if(isset($_FILES['data_json'])){
			try{
				if($_FILES['data_json']['tmp_name'] != ''){
					$extra_data = json_decode(file_get_contents($_FILES['data_json']['tmp_name']), true);
				}
			}catch(Exception $err){
				http_response_code(500);
			}
		}

		$apparent_ip = $_SERVER['REMOTE_ADDR'];
		//This is a fake nat ip
		if($apparent_ip == '10.100.11.10'){
			if(isset($extra_data['local_ip'])){
				$apparent_ip = $extra_data['local_ip'];
			}
		}

		//Register the Device if it was not in the register
		if($NrRecivedPlayers == 0){
			$player = new Player();
			$player->hardwareid = $player_id;
			$player->name = 'unnamed';
			$player->mainplayergroup = 1;//Set this to the right group
			$player->lastip = $apparent_ip;
			$player->lastcheckin = date("Y-m-d H:i:s");
			$internalPlayer_id = $player->save();

			$player = new Player($internalPlayer_id);
		}else{
			//If we have a registerd device update it's last checkin date
			$player->lastcheckin = date("Y-m-d H:i:s");
			$player->lastip = $apparent_ip;
			if($player->exec != ''){
				$json_resp['exec'] = $player->exec;
				$player->exec = '';
			}
			
			$player->save();
		}

		//If the device is running a old version tell it to update
		if(isset($_GET['version'])){
			if($APIV == 1){
				$player_version = intval($_GET['version']);
				if($player_version != $VERSION){
					echo('NEW_VERSION_AVALIBLE');
					exit();
				}
			}else{
				$VERSION = get_version($synced_script_folder);
				$player_version = $_GET['version'];
				if($player_version != $VERSION){
					$json_resp['upgrade'] = array_diff(scandir($synced_script_folder), array('..', '.'));
				}
			}
		}elseif(isset($_GET['upgrade'])){//If the device want update files give it the newest files
			if($APIV == 1){
				if($_GET['upgrade'] == 'get_videos'){
					readfile('/var/www/clients/device_script/get_videos.sh');
				}elseif($_GET['upgrade'] == 'play_videos'){
					readfile('/var/www/clients/device_script/play_videos.sh');
				}elseif($_GET['upgrade'] == 'upgrade_software'){
					readfile('/var/www/clients/device_script/upgrade_software.sh');
				}else{
					http_response_code(404);
				}
			}else{
				$scanned_directory = array_diff(scandir($synced_script_folder), array('..', '.'));
				foreach($scanned_directory AS $file_name){
					if($_GET['upgrade'] == $file_name){
						readfile($synced_script_folder.'/'.$file_name);
						break;
					}
				}
			}
			exit();
		}

		//If we have screen
		if(isset($_FILES['screen_png'])){
			try{
				if($_FILES['screen_png']['tmp_name'] != ''){
					$old_file = '/opt/screenshots/old/'.$player->_id.'.png';
					$new_file = '/opt/screenshots/'.$player->_id.'.png';
					rename($new_file, $old_file);
					
					$screen_data = file_get_contents($_FILES['screen_png']['tmp_name']);
					file_put_contents('/opt/screenshots/'.$player->_id.'.png', $screen_data);
				}
			}catch(Exception $err){
				http_response_code(500);
			}
		}
		
		//If we have exec results
		if(isset($_FILES['exec'])){
			try{
				if($_FILES['exec']['tmp_name'] != ''){
					$result = file_get_contents($_FILES['exec']['tmp_name']);
					file_put_contents('/opt/exec/'.$player->_id.'.txt', $result);
				}
			}catch(Exception $err){
				http_response_code(500);
			}
		}


	
		//If we have edid information try to pharse it
		if(isset($_FILES['edid'])){
			//var_dump($_FILES);
			try{
				ob_start();
				include("decode_data.php");
			}catch(Exception $err){
				http_response_code(500);
			}
			$out2 = ob_get_contents();
			file_put_contents('/tmp/decode_output.txt', $out2);
			ob_end_clean();
		}

		//Give the device the playergroup identifier that it is in
		$btsync_video_secret = strval($player->mainplayergroup);
		if($APIV == 2){
				$json_resp['group'] = $btsync_video_secret;
				$json_resp['group_name'] = '';
				$json_resp['player_id'] = $player->_id;
				$json_resp['player_name'] = $player->name;
				echo(json_encode($json_resp));
		}else{
			echo($btsync_video_secret);
		}
		exit();
	}else{
		//The device did not know the secret key
	}
}
//If you are authed you will not get here
http_response_code(403);
?>
digital signage
