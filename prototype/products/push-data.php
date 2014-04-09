<?php
require_once('../config.php');
require_once('../curl.php');

$fileContents = file_get_contents('../UpdateShopProducts.xml');
$searchArray  = array('[PARTNER_ID]','[PARTNER_PWD]','[USER_ID]','[USER_PWD]','[ERROR_LANGUAGE]','[PROD_INSERT]','[USER_PROD_ID]','[AB_PROD_ID]','[PROD_ANR]','[PROD_EAN]','[PROD_NAME]','[PROD_SHORT_DESC]','[PROD_QUANTITY]','[BASE_PRODUCT_TYPE]');

$productId = $_REQUEST['id'];
$query = 'SELECT * FROM products WHERE ProductId = "'.$productId.'"';
$result = mysql_query($query);
$pResult = mysql_fetch_assoc($result);

$prodInsert = 0;
if(empty($pResult['ABProductId'])){
	$prodInsert = 1;
	$pResult['ABProductId'] = $productId;
}
$replaceArray = array($glb_partner_id,$glb_partner_pwd,$glb_user_id,$glb_user_pwd,'EN',
$prodInsert,$productId,$pResult['ABProductId'],$pResult['ANR'],$pResult['EAN'],
$pResult['Name'],$pResult['ShortDesc'],$pResult['Quantity'],$pResult['ProductType']);

$baseProductXML = $ebayXML = '';
if($pResult['ProductType'] == 1 && !empty($pResult['ABProductId'])){
	$query = 'SELECT ABProductId,Quantity FROM products WHERE ParentABProductId = "'.$pResult['ABProductId'].'"';
	$res3 = mysql_query($query);
	$childFound = FALSE;
	$pos = 0;
	while ($info = mysql_fetch_assoc($res3)) {
		$childFound = TRUE;
		$default = 0;
		if(!$pos){
			$default = 1;
		}
		//create XML String
		$baseProductXML .= '<AddBaseProduct>
					          <ProductID>'.$info['ABProductId'].'</ProductID>
					          <ProductLabel><![CDATA[ Red ]]></ProductLabel>
					          <ProductPos>'.$pos.'</ProductPos>
					          <DefaultProduct>'.$default.'</DefaultProduct>
					          <ProductQuantity>'.$info['Quantity'].'</ProductQuantity>
        					</AddBaseProduct>
      						'.PHP_EOL;
        $ebayXML .= '<Variation>
             			<VariationName><![CDATA[ Color ]]></VariationName>
             			<VariationValues>
                			<ValidForProdID>'.$info['ABProductId'].'</ValidForProdID>
                			<VariationValue><![CDATA[ Red ]]></VariationValue>
                			<VariationPos>'.$pos.'</VariationPos>
             			</VariationValues>
          			</Variation>'.PHP_EOL;
		$pos++;
	}
	if($childFound){
		$baseProductXML = PHP_EOL.'<AddBaseProducts>'.PHP_EOL.$baseProductXML.PHP_EOL.'</AddBaseProducts>'.PHP_EOL;
		$ebayXML = PHP_EOL.'<UseeBayVariations>'.PHP_EOL.$ebayXML.PHP_EOL.'</UseeBayVariations>'.PHP_EOL;
		//get different XML file
		$fileContents = file_get_contents('../UpdateShopProductsVariation.xml');
		$searchArray = array_merge($searchArray,array('[ADD_BASE_PRODUCTS]','[EBAY_VARIATIONS]'));
		$replaceArray = array_merge($replaceArray,array($baseProductXML,$ebayXML));
	}
}

$fileContents = str_ireplace($searchArray, $replaceArray, $fileContents);

$response = get_AB_response($fileContents);
if($response['success']){
	//header ("Content-Type:text/xml");
	$xml = (array)simplexml_load_string($response['response']);
	if($xml['CallStatus'] == 'Success'){
		if($prodInsert == '1'){
		 $result = (array)$xml['Result'];
		 $result2 = (array)$result['NewProducts']->NewProduct->ProductID;
		 $ABProductId = $result2[0];
		 $query = 'UPDATE products SET ABProductId = "'.$ABProductId.'",UpdatedLocally = '.time().',UpdatedAB = '.time().' WHERE ProductId = "'.$productId.'"';
		 mysql_query($query);
		 $_SESSION['PUSH_STATUS'] = 'New product data inserted into AB';
		}else{
		 $_SESSION['PUSH_STATUS'] = 'Existing product data updated into AB';
		 $query = 'UPDATE products SET UpdatedLocally = '.time().',UpdatedAB = '.time().' WHERE ProductId = "'.$productId.'"';
		 mysql_query($query);
		}
		if($childFound){
		 $_SESSION['PUSH_STATUS'] = 'Variation data managed into AB';
		}
	}else{
	$_SESSION['PUSH_STATUS'] = 'Product information could not be sync with AB';
  }
}else{
 $_SESSION['PUSH_STATUS'] = 'Product information could not be sync with AB';
}
header('Location: '.$appPath.'/products');
?>