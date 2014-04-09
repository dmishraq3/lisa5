<?php
require_once('../config.php');
require_once('../curl.php');
include_once('../xml2array.php');
set_time_limit(0);

echo 'Call stated on '.date('d M, Y h:i:s A').'<br/>';
function get_shop_products($searchArray,$replaceArray){
  $fileContents = file_get_contents('../GetShopProducts.xml');
  $fileContents = str_ireplace($searchArray, $replaceArray, $fileContents);
  $response = get_AB_response($fileContents);
  return $response;
}

$pageNumber = 1;
$searchArray  = array('[PARTNER_ID]','[PARTNER_PWD]','[USER_ID]','[USER_PWD]','[ERROR_LANGUAGE]','[PAGE_NUMBER]','[MAX_SHOP_ITEMS]');

$replaceArray = array($glb_partner_id,$glb_partner_pwd,$glb_user_id,$glb_user_pwd,'EN',$pageNumber,MAX_SHOP_PRODUCTS_PER_PAGE);

$response = get_shop_products($searchArray,$replaceArray);
$productsArray = array();
$productIndex = 0;
if($response['success']){
	$returnArray = XML2Array::createArray($response['response']);
	if(isset($returnArray['Afterbuy']['Result']['ErrorList'])){
		pr($returnArray['Afterbuy']['Result']['ErrorList']);
	}else{
		$hasMoreProducts = $returnArray['Afterbuy']['Result']['HasMoreProducts'];
		$totalPages      = $returnArray['Afterbuy']['Result']['PaginationResult']['TotalNumberOfPages'];
		$totalItems      = $returnArray['Afterbuy']['Result']['PaginationResult']['TotalNumberOfEntries'];
		$currentPageNumber      = $returnArray['Afterbuy']['Result']['PaginationResult']['PageNumber'];
		//pr($returnArray['Afterbuy']['Result']['Products']['Product']);
		//die;
		foreach ($returnArray['Afterbuy']['Result']['Products']['Product'] as $key => $value) {
			$productsArray[$productIndex]['ProductID'] = $value['ProductID'];
			$productsArray[$productIndex]['Anr'] = $value['Anr'];
			$productsArray[$productIndex]['EAN'] = $value['EAN']['@cdata'];
			$productsArray[$productIndex]['Name'] = $value['Name']['@cdata'];
			$productsArray[$productIndex]['Description'] = $value['Description']['@cdata'];
				$productsArray[$productIndex]['ShortDescription'] = $value['ShortDescription']['@cdata'];
				$productsArray[$productIndex]['Stock'] = $value['Stock'];
				$productsArray[$productIndex]['Discontinued'] = $value['Discontinued'];
				$productsArray[$productIndex]['Quantity'] = $value['Quantity'];
				$productsArray[$productIndex]['ModDate'] = strtotime($value['ModDate']);
				$productIndex++;
		}
		//echo 'Page : #'.$pageNumber.'<br/>';
		//pr($returnArray['Result']);
		while ($hasMoreProducts 
			//|| $productIndex<=500
			) {
			//sleep for 0.5 secs
			//time_nanosleep(0, 500000000);
			$pageNumber++;
			$searchArray  = array('[PARTNER_ID]','[PARTNER_PWD]','[USER_ID]','[USER_PWD]','[ERROR_LANGUAGE]','[PAGE_NUMBER]','[MAX_SHOP_ITEMS]');
			$replaceArray = array($glb_partner_id,$glb_partner_pwd,$glb_user_id,$glb_user_pwd,'EN',$pageNumber,MAX_SHOP_PRODUCTS_PER_PAGE);
			$response = get_shop_products($searchArray,$replaceArray);
			$returnArray = XML2Array::createArray($response['response']);

			$hasMoreProducts = $returnArray['Afterbuy']['Result']['HasMoreProducts'];
			foreach ($returnArray['Afterbuy']['Result']['Products']['Product'] as $key => $value) {
			$productsArray[$productIndex]['ProductID'] = $value['ProductID'];
			$productsArray[$productIndex]['Anr'] = $value['Anr'];
			$productsArray[$productIndex]['EAN'] = $value['EAN']['@cdata'];
			$productsArray[$productIndex]['Name'] = $value['Name']['@cdata'];
			$productsArray[$productIndex]['Description'] = $value['Description']['@cdata'];
				$productsArray[$productIndex]['ShortDescription'] = $value['ShortDescription']['@cdata'];
				$productsArray[$productIndex]['Stock'] = $value['Stock'];
				$productsArray[$productIndex]['Discontinued'] = $value['Discontinued'];
				$productsArray[$productIndex]['Quantity'] = $value['Quantity'];
				$productsArray[$productIndex]['ModDate'] = strtotime($value['ModDate']);
				$productIndex++;
			}
			//echo 'Page : #'.$pageNumber.'<br/>';
			//pr($returnArray['Result']);
		}
	}
}else{
  echo 'Curl Error Occurred';
}

if(is_array($productsArray) && count($productsArray)){
	foreach ($productsArray as $key => $value) {
		//check for existing items
		$query = 'SELECT COUNT(ABProductId) AS cnt FROM products WHERE ABProductId = "'.$value['ProductID'].'"';
		$result = mysql_query($query);
		$data = mysql_fetch_assoc($result);
		if($data['cnt']){//update
			$query  = 'UPDATE products ';
			$query .= ' SET ';
			$query .= ' Name = "'.$value['Name'].'",';
			$query .= ' ANR = "'.$value['Anr'].'",';
			$query .= ' EAN = "'.$value['EAN'].'",';
			$query .= ' ShortDesc = "'.$value['ShortDescription'].'",';
			$query .= ' Quantity = "'.$value['Quantity'].'",';
			$query .= ' UpdatedAB = "'.$value['ModDate'].'",';
			$query .= ' UpdatedLocally = '.$value['ModDate'];
			$query .= ' WHERE ABProductId = "'.$value['ProductID'].'"';
			mysql_query($query);

		}else{//insert
			$query  = 'INSERT INTO products ';
			$query .= ' (ABProductId,Name,ANR,EAN,ShortDesc,Quantity,CreatedTime,UpdatedLocally,UpdatedAB)';
			$query .= ' VALUES ';
			$query .= ' ("'.$value['ProductID'].'","'.$value['Name'].'","'.$value['Anr'].'","'.$value['EAN'].'","'.$value['ShortDescription'].'","'.$value['Quantity'].'",'.$value['ModDate'].','.$value['ModDate'].','.$value['ModDate'].')';
			mysql_query($query);
		}
	}
	echo '<h2>Operation completed successfully</h2>';
}
echo 'Call ended on '.date('d M, Y h:i:s A').'<br/>';
//pr($productsArray)
?>