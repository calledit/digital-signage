<?php

require_once('libs/php-edid-decode/php-edid-decode.php');
$edid_data = file_get_contents($_FILES['edid']['tmp_name']);
if(!empty($edid_data)){
	$edidDecode = new EdidDecode();
	$edidDecode->_output = false;
	$edidDecode->main($edid_data, true);//calle: disable as it crached way to often

	var_dump($edidDecode->result);
	if(isset($edidDecode->result["max-image-size"]) && isset($edidDecode->result["name"]) && isset($edidDecode->result["manufacturer_name"])){
		$ImageSize = explode(' x ', $edidDecode->result["max-image-size"]);
		$ImageWidth = intval(preg_replace('/\D/', '', $ImageSize[0]));
		$ImageHeight = intval(preg_replace('/\D/', '', $ImageSize[1]));

		$player->screen_width = $ImageWidth;
		$player->screen_height = $ImageHeight;
		$player->screen_active = 1;
		$player->screen_manufacturer_name = $edidDecode->result["manufacturer_name"];
		$player->screen_name = $edidDecode->result["name"];
		$player->screen_product_code = $edidDecode->result["product_code"];

	}else{
		$player->screen_active = 0;
	}
	$player->save();
}

?>
