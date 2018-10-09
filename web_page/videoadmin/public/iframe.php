<?php
$state = array('weburl' => 'https://stats.gant.com/cbr_show_last_sale/');
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
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
		<iframe src="<?= $state['weburl'] ?>" frameBorder="0"></iframe>
	</body>
</html>
