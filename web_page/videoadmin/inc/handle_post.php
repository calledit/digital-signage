<?php

if(isset($_POST['addgroup']) && !empty($_POST['addgroup'])){
	$NewGroup = new Playergroup();
	$NewGroup->name = strval($_POST['addgroup']);
	$NewGroup->user = $USER->_id;
	$NewGroup->save();
	mkdir('/opt/rsync_share/'.$NewGroup->_id);
}
if(isset($_POST['alterplayername'])){
	$ChnagePlayer = intval($_POST['alterplayername']);
	$PlayerName = strval($_POST['playername']);

	$player = new Player($ChnagePlayer);
	$player->name = $PlayerName;
	$player->save();
}

if(isset($_POST['selectplayerowner'])){
	$ChnagePlayer = intval($_POST['selectplayerowner']);
	$UserId = intval($_POST['owner']);

	$player = new Player($ChnagePlayer);
	$player->user = $UserId;
	$player->save();
}

if(isset($_POST['selectplayermaingroup'])){
	$ChnagePlayer = intval($_POST['selectplayermaingroup']);
	$MainGId = intval($_POST['maingroup']);

	$player = new Player($ChnagePlayer);
	$player->mainplayergroup = $MainGId;
	$player->save();
}

if(isset($_POST['delete_playlist'])){
	$AlterGroupID = intval($_POST['delete_playlist']);
	$PlayerGroup = new Playergroup($AlterGroupID);
	$group_players = Player::GetListByProperty(array('mainplayergroup' => $PlayerGroup->_id));
	if($PlayerGroup->_id == 1){
		throw new Exception("i cant delete playlist nr 1 as it is the default playlist");
	}
	if(count($group_players) == 0){
		
		//empty the videos in the playlist
		if(file_exists('/opt/rsync_share/'.$PlayerGroup->_id)){
			rrmdir('/opt/rsync_share/'.$PlayerGroup->_id);
		}
		$PlayerGroup->remove();
		header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		exit;
	}else{
		throw new Exception("i cant delete playlist that is beeing used by players");
	}
}

if(isset($_POST['selectvideosforgroup']) && isset($_POST['groupvideos']) && is_array($_POST['groupvideos']) && isset($_POST['groupdate'])){
	$AlterGroupID = intval($_POST['selectvideosforgroup']);
	$PlayerGroup = new Playergroup($AlterGroupID);

	if($USER->_id == $PlayerGroup->user || $USER->admin){
		if($PlayerGroup->public != $_POST['public']){
			$PlayerGroup->public = $_POST['public'];
			$PlayerGroup->save();
		}
	}
	$GroupDate = intval($_POST['groupdate']);
	if(isset($_POST['add_date'])){
		mkdir('/opt/rsync_share/'.$AlterGroupID.'/'.time());
	}elseif(isset($_POST['delete_date']) ){
		if(file_exists('/opt/rsync_share/'.$AlterGroupID.'/'.$GroupDate)){
			rrmdir('/opt/rsync_share/'.$AlterGroupID.'/'.$GroupDate);
		}
	}elseif(isset($_POST['save_date']) ){
		$CurrentVideos = array_diff(scandir('/opt/rsync_share/'.$AlterGroupID.'/'.$GroupDate), array('..', '.'));
		$VideosToRemove = array_diff($CurrentVideos, $_POST['groupvideos']);
		$VideosToAdd = array_diff($_POST['groupvideos'], $CurrentVideos);

		//Remove Old videos
		foreach($VideosToRemove AS $VideoFile){
			if(file_exists('/opt/rsync_share/'.$AlterGroupID.'/'.$GroupDate.'/'.$VideoFile)){
				unlink('/opt/rsync_share/'.$AlterGroupID.'/'.$GroupDate.'/'.$VideoFile);
			}
		}
		//Add new videos
		foreach($VideosToAdd AS $VideoFile){
			if(is_file('/opt/videos/'.$VideoFile)){
				link('/opt/videos/'.$VideoFile, '/opt/rsync_share/'.$AlterGroupID.'/'.$GroupDate.'/'.$VideoFile);
			}
		}
		if(isset($_POST['videogroupstartdate'])){
			$newDate = strtotime($_POST['videogroupstartdate']);
			if($newDate != $GroupDate){
				//If the move to destination already exists we cant allow the move to be done
				if(file_exists('/opt/rsync_share/'.$AlterGroupID.'/'.$newDate)){
					//Cant have to things playing at the same time
				}else{
					rename('/opt/rsync_share/'.$AlterGroupID.'/'.$GroupDate, '/opt/rsync_share/'.$AlterGroupID.'/'.$newDate);
				}
			}
		}
	}
}

if(isset($_POST['approve_video'])){
	if(in_array($_POST['approve_video'], $Files_for_approval)){
		rename('/opt/files_for_approval/'.$_POST['approve_video'], '/opt/videos/'.$_POST['approve_video']);
	}
}

if(isset($_POST['delete_video'])){
	if(in_array($_POST['delete_video'], $AvalibleVideos)){
		$video_usage = array();
		foreach($Videos AS $group_id => $date_folders){
			foreach($date_folders AS $datefolder => $date_videos){
				if(in_array($_POST['delete_video'], $date_videos)){
					$video_usage[] = $group_id;
				}
			}
		}
		if(count($video_usage) == 0){
			unlink('/opt/videos/'.$_POST['delete_video']);
		}else{
			throw new Exception("Cant delete video that is beeing used by playergroups");
		}
	}
}

//If somthing has changed we generate a new config file
if(count($_POST) != 0){
	$players = Player::GetListByProperty(array());
	$playergroups = Playergroup::GetListByProperty(array());
	GenrateNewConfigFile($players, $playergroups);
	header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
}
