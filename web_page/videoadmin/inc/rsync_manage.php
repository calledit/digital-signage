<?php

function GenerateGroupConf($Group, $playersInGroup){
	
	$sharePath = '/opt/rsync_share/'.$Group->_id;

	if(!is_dir($sharePath)){
		if(!mkdir($sharePath, 0777)){
			throw new Exception("Could not create new share dir");
		}
		chmod($sharePath, 0777);
	}

	$confText = "[group_".$Group->_id."]\n";
	$confText .= "comment = ".$Group->comment."\n";
	$confText .= "path = ".$sharePath."\n";
	$confText .= "read only = yes\n";
	$confText .= "list = no\n";
	$confText .= "uid = nobody\n";
	$confText .= "gid = nogroup\n";
	$confText .= 'secrets file = /opt/rsync_config/secrets/'.$Group->_id.".secret\n";
	$confText .= "auth users = *\n";
	$confText .= "strict modes = false\n";
	$confText .= "\n";
	return($confText);
}



function GenerateRsyncSecrets($playersInGroup){
	global $SecretKey;
	$secret = "";
	foreach($playersInGroup AS $player_id => $player){
		$playerAuthCode =  hash('sha256', $player->hardwareid.'_'.$SecretKey."\n");

		$rsyncUser = sha1("user_".$player->hardwareid."\n");
		$rsyncPass =  sha1("password_".$playerAuthCode."\n");

		$secret .= $rsyncUser.":".$rsyncPass."\n";
		
	}
	return($secret);
}

function GenrateNewConfigFile($players, $playergroups){
	$ConfigText = "[misc]\n";
	$ConfigText .= "path = /opt/misc_sync\n";
	$ConfigText .= "read only = yes\n";
	$ConfigText .= "list = no\n";
	$ConfigText .= "\n";

	$SecretFiles = array();
	foreach($playergroups AS $Group_id => $group){
		$playersInGroup = array();
		foreach($players AS $player_id => $player){
			if($player->mainplayergroup == $Group_id){
				$playersInGroup[$player_id] = $player;
			}
		}
		$ConfigText .= GenerateGroupConf($group, $playersInGroup);
		$SecretFiles[$Group_id] = GenerateRsyncSecrets($playersInGroup);
	}
	$couldWriteConfig = file_put_contents('/opt/rsync_config/rsyncd.conf', $ConfigText, LOCK_EX);
	if($couldWriteConfig === FALSE){
		throw new Exception("Could not save rsync config file");
	}
	
	foreach($SecretFiles AS $group_id => $secrets){
		$couldWriteSecrets = file_put_contents('/opt/rsync_config/secrets/'.$group_id.'.secret', $secrets);
		if($couldWriteSecrets === FALSE){
			throw new Exception("Could not secrets file group: ".$group_id);
		}
	}
}


?>
