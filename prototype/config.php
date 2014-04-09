<?php
session_start();
error_reporting(1);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Kolkata');
//Developer
$glb_user_id = 'bdip191';
$glb_user_pwd = 'Q3tech123';
$glb_partner_id = '106488';
$glb_partner_pwd = 'testing123';

//Client
$glb_user_id = 'schuhe-lilulei';
$glb_user_pwd = 'testlilulei*1';
$glb_partner_id = '1000005499';
$glb_partner_pwd = 'VdfpLcOtelINaeiCER0QxXc8x';
$global_AB_url = 'https://api.afterbuy.de/afterbuy/ABInterface.aspx';

$appPath = 'http://115.113.189.49/ab';
$appImgPath  = $appPath.'/images/';

$dbServer = 'localhost';
$dbUserName = 'root';
$dbPwd = 'Q3tech123';
$dbName = 'afterbuy';

$dbConn = mysql_connect($dbServer,$dbUserName,$dbPwd);
$dbSel = mysql_select_db($dbName,$dbConn);

define("MAX_SHOP_PRODUCTS_PER_PAGE",100);

function pr($val){
	echo '<pre>';print_r($val);echo '</pre>';
}

function XML2Array(SimpleXMLElement $parent){
	//return json_decode(json_encode((array) $parent), 1);
    $array = array();
    foreach ($parent as $name => $element) {
        ($node = & $array[$name])
            && (1 === count($node) ? $node = array($node) : 1)
            && $node = & $node[];

        $node = $element->count() ? XML2Array($element) : trim($element);
    }
    return $array;
}

?>