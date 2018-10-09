<?php
ini_set('display_errors', 1);
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/videoadmin');



function xss_clean(&$arr_2_clean){
	foreach ($arr_2_clean as $nri => $v) {
		if(is_array($arr_2_clean[$nri])){
			xss_clean($arr_2_clean[$nri]);
		}else{
			$arr_2_clean[$nri] = htmlspecialchars($v);
		}
	}
}

function rrmdir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                rrmdir($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}

/*clean most xss Things*/
xss_clean($_GET);
xss_clean($_POST);

@mkdir('/tmp/tmpupldir/');
@mkdir('/tmp/conversionlog/');

require_once('/var/www/clients/config.php');

require_once('inc/login_session.php');

require_once('libs/BigUpload/inc/bigUpload.php');

$Uploder = new BigUpload("/tmp/tmpupldir/", "/opt/uploaded_raw_files/");

if($Uploder->CheckIncomingFile()){
	$file_name = $Uploder->HandleIncomingFile();

	//Start conversion of video file
	$_GET['convert'] = $file_name;
	include('../inc/convert.php');
	exit;
}

require_once('inc/rsync_manage.php');



$PAGE = 'overview';
if(isset($_GET['page'])){
	$views = array_diff(scandir('views'), array('..', '.'));
	$InPAGE = strval($_GET['page']);
	if(in_array($InPAGE.'.php', $views)){
		$PAGE = $InPAGE;
	}
}

$users = array();
if($USER->admin){
	$users = User::GetListByProperty(array());
}

$players = Player::GetListByProperty(array());

//Fiter any only players that the user should be able to see
$visible_players = array();
if($USER->admin){
	$visible_players = $players;
}else{

	//fill $visible_players with the players that we own
	//$player_owners = Player_owner::GetListByProperty(array('user' => $USER->_id)); //for future use with owner groups
	foreach($players AS $_id => $player){
		if($player->user == $USER->_id){
			$visible_players[$_id] = $player;
		}
		/* //for future use with owner groups
		foreach($player_owners AS $owner_id => $player_owner){
			if($player_owner->player == $_id){
				$visible_players[$_id] = $player;
			}
		}
		 */
	}
}

//Fiter any only playergroups that the user should be able to see
$playergroups = Playergroup::GetListByProperty(array());
$visible_playergroups = array();
if($USER->admin){
	$visible_playergroups = $playergroups;
}else{
	foreach($playergroups AS $_id => $playergroup){
		if($playergroup->user == $USER->_id || $playergroup->public){
			$visible_playergroups[$_id] = $playergroup;
		}
	}
}


$AvalibleVideos = array_diff(scandir('/opt/videos'), array('..', '.'));
$UploadedVideos = array_diff(scandir('/opt/uploaded_raw_files'), array('..', '.'));
$Files_for_approval = array_diff(scandir('/opt/files_for_approval'), array('..', '.'));
$ConvertingVideos = array_diff(scandir('/opt/converting_files'), array('..', '.'));
foreach($UploadedVideos AS $id => $videoFile){
	$LogFile = '/tmp/conversionlog/'.$videoFile.'.log';
	$LogData = file_get_contents($LogFile);
	if($LogData){
		$HeaderLines = explode("\n", $LogData);
		foreach($HeaderLines AS $Line){
			
		}
		$PrecentLines = explode("\r", $LogData);
		$FinalStuff = array_pop($PrecentLines);
		$LastPrecentLine = array_pop($PrecentLines);
		$UploadedVideos[$id] = array(
			'name' => $videoFile,
			'lastLine' => $LastPrecentLine
		);
		//have we reached the end out the logfile output
		if(strpos($FinalStuff, 'muxing overhead:') !== FALSE){
			$LastPrecentLine = "Conversion Done";
			rename('/opt/converting_files/'.$videoFile.'.mp4', '/opt/files_for_approval/'.$videoFile.'.mp4');
			unlink('/opt/uploaded_raw_files/'.$videoFile);
		}else{
			//File is still converting
			$LogFileInfo = stat($LogFile);

			//If the file has not been touched in 20 minutes we asume the conversion has failed
			if($LogFileInfo['mtime']<(time()-60*20)){
				unlink('/opt/uploaded_raw_files/'.$videoFile);
				unlink('/opt/converting_files/'.$videoFile.'.mp4');
				throw new Exception("Failed to convert video file");
			}
		}
	}else{
		unlink('/opt/uploaded_raw_files/'.$videoFile);
		throw new Exception("Could not read ffmpeg log file");
	}
}

$Videos = array();

//Collect all playable videos and their playdate
foreach($playergroups AS $group_id => $group){
	$date_folders = array_diff(scandir('/opt/rsync_share/'.$group_id), array('..', '.'));
	$Videos[$group_id] = array();
	foreach($date_folders AS $datefolder){
		//Temporary only take fixed folders
		if(is_dir('/opt/rsync_share/'.$group_id.'/'.$datefolder)){
			$Videos[$group_id][$datefolder] =  array_diff(scandir('/opt/rsync_share/'.$group_id.'/'.$datefolder), array('..', '.'));
		}
	}
}


require_once('inc/handle_post.php');

function toScrenSize($playerInfo){
		if(isset($playerInfo->screen_width)){
			return(round(sqrt(pow($playerInfo->screen_width, 2) + pow($playerInfo->screen_height, 2))*0.393700787)).'"';
		}
		return("unknown");
}

$BODY_FILE = 'subviews/body_template.php';
include('subviews/html_template.php');
?>
