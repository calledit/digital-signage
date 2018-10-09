<?php

session_start();

if(!isset($_SESSION['user']) && isset($_POST['email']) && isset($_POST['password'])){
	$users = User::GetListByProperty(array('email' => $_POST['email'], 'password' => $_POST['password']));
	if(count($users) == 1){
		$_SESSION['user'] = array_pop($users);
	}
	//$_SESSION['user'] = new User(2);
   /*	array(
		'_id' => 1,
		'admin' => 1,
		'name' => 'Calle',
	);
	*/
	header('Location: https://'.$_SERVER['HTTP_HOST'].'/');
	exit;
}

if(isset($_SESSION['user']) && isset($_GET['logout_now'])){
	unset($_SESSION['user']);
}

$USER = NULL;
if(isset($_SESSION['user'])){
	$USER = $_SESSION['user'];
}
session_write_close();

//Make sure that we are loged in exit if we are not
if(!isset($USER)){
	if(isset($_GET['page']) && $_GET['page'] == 'login'){
		$BODY_FILE = 'subviews/login.php';
		include('subviews/html_template.php');
	}else{
		header('Location: https://'.$_SERVER['HTTP_HOST'].'/?page=login');
	}
	exit;
}
