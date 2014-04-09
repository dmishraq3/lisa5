<?php
require_once('./config.php');
require_once('./curl.php');

$fileContents = file_get_contents('./UpdateShopProducts.xml');
$searchArray  = array('[PARTNER_ID]','[PARTNER_PWD]','[USER_ID]','[USER_PWD]','[ERROR_LANGUAGE]');
$replaceArray = array($glb_partner_id,$glb_partner_pwd,$glb_user_id,$glb_user_pwd,'EN');
$fileContents = str_ireplace($searchArray, $replaceArray, $fileContents);

$response = get_AB_response($fileContents);
if($response['success']){
	header ("Content-Type:text/xml");
}
echo $response['response'];
?>