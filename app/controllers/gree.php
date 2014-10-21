<?php
	require_once ('../libs/Greesdk/Platform.php');

	$a = new Greesdk_Platform('ab6b4509eebd','7e2e090af6869aaad87dd40894a100ab');
	$b = $a->People;
	$c = $b->get();
	$c = serialize($c);
	
	$file_zh = fopen("../zhuyong.txt","w+");
	fwrite($file_zh,$c);
	fclose($file_zh);
?>