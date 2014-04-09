<?php
function get_AB_response($fileContents,$url= ''){
	$ch = curl_init();
	if(empty($url)){
		$url = $GLOBALS['global_AB_url'];
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 600);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContents);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));
	$result = curl_exec($ch);
	if(curl_errno($ch)){
	 $arr['success']  = FALSE;
	 $arr['response'] = 'Error : '.curl_errno($ch).'<br/>Details : '.curl_error($ch).'<br/>';
	}else{
	 $arr['success']  = TRUE;
	 $arr['response'] = $result;
	 //header ("Content-Type:text/xml");
	 //echo $result;
	}
	curl_close($ch);
	return $arr;
}
?>