<?php
	$json = $_POST['json'];
	if(json_decode($json) != null)
	{
		$file = fopen("../users/fabian/".date("Ymd G-i-s").".json","w+");
		fwrite($file,$json);
		fclose($file);
	}
?>
