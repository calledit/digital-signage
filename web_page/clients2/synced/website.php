<?php

include("/home/pi/scripts/synced/common.php");
$state = array();

//Get Avalible Video Play dates
$state['avalible_dates'] = get_play_dates();

//Get the folder that we should be playing from
$state['folder_to_play'] = get_what_should_be_played($state['avalible_dates']);
$state['keyboard_connected'] = is_keyboard_connected();
$state['powersupply_ok'] = is_powersupply_ok();
$state['weburl'] = play_file_is_url($state['folder_to_play']);

//Man message to show user
$main_message = 'keyboard connected please enter config';
if(!$state['keyboard_connected']){
	if(!$state['folder_to_play']){
		$main_message = 'no videos synced';
	}
}

$state['server_data'] = array(
	'player_id' => 'not recived since boot',
	'player_name' => 'not recived since boot',
	'is_connected' => false,
);
if(file_exists('/tmp/https_response')){
	$jresp = json_decode(file_get_contents('/tmp/https_response'), true);
	if($jresp){
		$stat_info = stat('/tmp/https_response');
		//If the file was edited under the last 7 minutes
		if($stat_info['mtime'] > $cur_time-((5*60)+(121))){
			$state['server_data']['is_connected'] = true;
		}
		$state['server_data']['player_id'] = $jresp['player_id'];
		$state['server_data']['player_name'] = $jresp['player_name'];
	}
}

//Get list of interfaces
$state['net_interfaces'] = get_network_interfaces();
$state['wlan_interface'] = get_wlan_interface($state['net_interfaces']);

//Get info about the interfaces
$state['interface_info'] = array();
foreach($state['net_interfaces'] AS $interface){
	if($interface != 'lo'){
		$state['interface_info'][$interface] = get_network_interface_info($interface);
	}
}

if($state['keyboard_connected']){

	if($state['wlan_interface']){
		$state['wlan_networks'] = get_wlan_networks($state['wlan_interface']);
		$state['wifi_cred'] = read_wifi_cred();
	}
}
$state['date'] = date("Y-m-d H:i");

if(isset($_GET['ajax'])){
	echo json_encode($state);
	exit;
}

if(isset($_POST['wifi_psk']) || isset($_POST['wifi_ssid'])){
	$state['wifi_cred']['ssid'] = $_POST['wifi_ssid'];
	$state['wifi_cred']['psk'] = $_POST['wifi_psk'];
	write_wifi_cred($state['wifi_cred']);

	reboot_network_interface($state['wlan_interface']);
}

if(!$state['keyboard_connected']){
	if($state['weburl']){
?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			body,html,iframe{
				margin:0;
				height:100%;
				width:100%;
				padding:0px;
				overflow:hidden;
			}
		</style>
		<script>
			//Reload the iframe every 3 hours
			setInterval(function(){
					document.getElementById('iframe').src = document.getElementById('iframe').src
				}, 1000*3600*3);
		</script>
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
		<iframe id="iframe" src="<?= $state['weburl'] ?>" frameBorder="0"></iframe>
	</body>
</html>
<?php
		//header('Location: '.$state['weburl']);
		exit;
	}
}


?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Media Player admin</title>

		<link href="synced/bootstrap.min.css" rel="stylesheet">
		<script src="synced/jquery-1.11.3.min.js"></script>
		<script>
var initial_state = <?= json_encode($state) ?>;

var initial_state_str = JSON.stringify(initial_state);
//check for changes every 2 seconds
setInterval(function(){
	$.getJSON("?ajax=status", function(state){
		document.getElementById("date_holder").innerHTML = state['date']
		state['date'] = initial_state['date']
		//if the keyboard state changes reload the page
		if(initial_state_str != JSON.stringify(state)){
			document.location.reload();
		}
		//alert(initial_state_str);
		/*
		//is this page setup for config
		if(initial_state['keyboard_connected']){
			//If there is no keyboard connected reload the page
			if(!state['keyboard_connected']){
				document.location.reload();
			}
		}*/
	});
},2000);
		</script>
	</head>
	<body>
<?php
/*
//for debuging when ssh is not avalible
exec('cat /var/log/syslog', $out);
echo implode("<br>\n", $out);

exec('cat /var/log/auth.log', $out);
echo implode("<br>\n", $out);
 */
?>
		<div class="container-fluid">
			<div class="page-header">
				<h1><?= $main_message ?></h1>
			</div>
			<div class="row">
				<div class="col-md-6">
					<h3>General Status</h3>
					<!-- <p><b>powersupply:</b> <?= $state['powersupply_ok']?'OK':'<span class="label label-danger">Under powerd</span>' ?></p> -->
					<p><b>date:</b> <span id="date_holder"><?= date("Y-m-d H:i");  ?></span></p>
					<p><b>player id:</b> <?= $state['server_data']['player_id']  ?></p>
					<p><b>player name:</b> <?= $state['server_data']['player_name']  ?></p>
					<p><b>connected:</b> <?= $state['server_data']['is_connected']?'<span class="label label-success">YES</span>':'<span class="label label-danger">NO</span>' ?></p>
				</div>
			</div>
			<hr>
			<div class="row">
<?php foreach($state['interface_info'] AS $interface => $interface_info): ?>
				<div class="col-md-6">
					<h3><?= $interface ?></h3>
<?php foreach($interface_info AS $attr => $value): ?>
					<p><b><?= $attr ?>:</b> <?= $value ?></p>
<?php endforeach; ?>
				</div>
<?php endforeach; ?>
			</div>
			<hr>
			<div class="row">
				<div class="col-md-6">
<?php	if($state['keyboard_connected']): ?>
					<form method="post" action="">
<?php	if($state['wlan_interface']): ?>
						<div class="form-group">
							<label for="wifinet">WIFI network</label>
							<select class="form-control" id="wifinet" name="wifi_ssid">
<?php foreach($state['wlan_networks'] AS $wlan_network): ?>
								<option <?= ($state['wifi_cred']['ssid'] == $wlan_network)?'selected':'' ?> value="<?= htmlspecialchars($wlan_network)?>"><?= htmlspecialchars($wlan_network)?></option>
<?php endforeach; ?>
							</select>
						</div>
						<div class="form-group">
							<label for="wifipwd">WIFI password</label>
							<input class="form-control" type="text" id="wifipwd" name="wifi_psk" value="<?= $state['wifi_cred']['psk'] ?>" placeholder="WIFI password">
						</div>
<?php	endif; ?>
						<button type="submit" class="btn btn-default">Save</button>
					</form>
<?php	endif; ?>
				</div>
			</div>
		</div>
	</body>
</html>
