<?php

$VERSION = 20;
$DatabaseName = 'storevideo';
/*Connect to the database*/
$MY_SQL_Handle = new mysqli('localhost', $DatabaseName, 'PASSWORD', $DatabaseName);
if(!$MY_SQL_Handle){
        throw new Exception('MY SQL Database connection failed');
}
$MY_SQL_Handle->set_charset('utf8');

require_once("libs/orm-static/db_classes.php");

//Update db classes only if this is an admin person
if(false){
	SaveDBClassesToFile($DatabaseName, '/var/www/clients/generated/generated_db_classes.php', $MY_SQL_Handle);
}


require_once("/var/www/clients/generated/generated_db_classes.php");


$SecretKey = 'SALT12646fghjk';


function get_version($directory){
	$scanned_directory = array_diff(scandir($directory), array('..', '.'));
	$files_content = "";
	foreach($scanned_directory AS $file_name){
		if(is_readable($directory.'/'.$file_name)){
			$files_content .= $file_name.':'.file_get_contents($directory.'/'.$file_name)."---";
		}
	}
	return(hash('sha1', $files_content));
}
