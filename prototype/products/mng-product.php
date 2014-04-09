<?php
require_once('../config.php');
require_once('../header.php');

$editProduct = FALSE;
if(!isset($_REQUEST['id']) || empty($_REQUEST['id'])){
	$autoNum = substr(number_format(time() * rand(),0,'',''),
            0,10);
	$productId = 0;
	$productArr['Name'] = '';
	$productArr['ANR'] = $autoNum;
	$productArr['EAN'] = 'EAN'.$autoNum;
	$productArr['ShortDesc'] = '';
	$productArr['LongDesc'] = '';
	$productArr['Quantity'] = '';
	$productArr['InStock'] = 1;
	$productArr['Discontinued'] = 0;
	if(isset($_REQUEST['prod_name'])){
		$productArr['Name'] = $_REQUEST['prod_name'];
	}
	if(isset($_REQUEST['prod_anr'])){
		$productArr['ANR'] = $_REQUEST['prod_anr'];
	}
	if(isset($_REQUEST['prod_ean'])){
		$productArr['EAN'] = $_REQUEST['prod_ean'];
	}
	if(isset($_REQUEST['prod_short_desc'])){
		$productArr['ShortDesc'] = $_REQUEST['prod_short_desc'];
	}
	if(isset($_REQUEST['prod_quantity'])){
		$productArr['Quantity'] = $_REQUEST['prod_quantity'];
	}
}else{
	$editProduct = TRUE;
	$productId = $_REQUEST['id'];
	$query = 'SELECT * FROM products WHERE ProductId = "'.$productId.'"';
	$result = mysql_query($query);
	$pResult = mysql_fetch_assoc($result);
	$productArr['Name'] = $pResult['Name'];
	$productArr['ANR'] = $pResult['ANR'];
	$productArr['EAN'] = $pResult['EAN'];
	$productArr['ShortDesc'] = $pResult['ShortDesc'];
	$productArr['LongDesc'] = $pResult['LongDesc'];
	$productArr['Quantity'] = $pResult['Quantity'];
	$productArr['InStock'] = $pResult['InStock'];
	$productArr['Discontinued'] = $pResult['Discontinued'];
}

if(isset($_POST) && count($_POST)){
	$dProductId = $_REQUEST['id'];
	$prod_name = trim($_REQUEST['prod_name']);
	$prod_anr = trim($_REQUEST['prod_anr']);
	$prod_ean = trim($_REQUEST['prod_ean']);
	$prod_short_desc = trim($_REQUEST['prod_short_desc']);
	$prod_quantity = trim($_REQUEST['prod_quantity']);
	$ProductType = $_POST['product_type'];
	$error = false;
	if(empty($prod_name)){
		$error = true;
		echo 'Error : Please enter product name';
	}
	if(empty($prod_anr) && !$error){
		$error = true;
		echo 'Error : Please enter product ANR';
	}
	if(empty($prod_ean) && !$error){
		$error = true;
		echo 'Error : Please enter product EAN';
	}
	if(empty($prod_short_desc) && !$error){
		$error = true;
		echo 'Error : Please enter product short description';
	}
	if(empty($prod_quantity) && !$error){
		$error = true;
		echo 'Error : Please enter product quantity';
	}
	if(!$error){
		if(empty($dProductId)){
			$query  = 'INSERT INTO products ';
			$query .= ' (ProductType,Name,ANR,EAN,ShortDesc,Quantity,CreatedTime,UpdatedLocally)';
			$query .= ' VALUES ';
			$query .= ' ("'.$ProductType.'","'.$prod_name.'","'.$prod_anr.'","'.$prod_ean.'","'.$prod_short_desc.'","'.$prod_quantity.'",'.time().','.time().')';
			mysql_query($query);
			$dProductId = mysql_insert_id(); 
		}else{
			$parent_product_id = $_POST['parent_product_id'];
			if(empty($parent_product_id)){
				$parent_product_id  = 'NULL';
			}
			$query  = 'UPDATE products ';
			$query .= ' SET ';
			$query .= ' Name = "'.$prod_name.'",';
			$query .= ' ParentABProductId = '.$parent_product_id.',';
			$query .= ' ANR = "'.$prod_anr.'",';
			$query .= ' EAN = "'.$prod_ean.'",';
			$query .= ' ShortDesc = "'.$prod_short_desc.'",';
			$query .= ' Quantity = "'.$prod_quantity.'",';
			$query .= ' UpdatedLocally = '.time();
			$query .= ' WHERE ProductId = "'.$dProductId.'"';
			mysql_query($query);
		}
		header('Location: '.$appPath.'/products/push-data.php?id='.$dProductId);
		/*
		if($_POST['product_type'] == '1'){
          header('Location: '.$appPath.'/products/push-data.php?id='.$dProductId);
		}else{
		  header('Location: '.$appPath.'/products');
		}
		*/
		exit;
	}
}
?>
<tr>
	<td >
		<div style="min-height:445px;border:0px solid red;">
		<form name="frm1" id="frm1" method="post" onsubmit="return postForm();">
		<input type="hidden" name="id" value="<?php echo $productId; ?>" />
		<table style="width:50%;border-collapse:collapse" border="1">
			<tr>
				<td>Product Type</td>
				<td>:</td>
				<td>
				<?php
				 if(!$editProduct){
				 	?>
				 	<select name="product_type">
				 		<option value="0">Standard Product</option>
				 		<option value="1">Variation Set</option>
				 	</select>
				 	<?php
				 }else{
				 	if($pResult['ProductType'] == '0'){
				 		echo 'Standard Product';
				 	}else{
				 		echo 'Variation Set';
				 	}
				 	?>
				 	<input type="hidden" name="product_type" value="<?php echo $pResult['ProductType'];?>" />
				 	<?php
				 }
				?>
				</td>
			</tr>
			<?php
			 if($editProduct && $pResult['ProductType']=='0'){
			?>
			<tr>
				<td>Parent Product</td>
				<td>:</td>
				<td>
				<?php
				 if(empty($pResult['ParentABProductId'])){
				 	$query = 'SELECT Name,ABProductId FROM products 
				 	WHERE ABProductId IS NOT NULL AND ProductType = 1';
				 	$result2 = mysql_query($query);
				 	?>
				 	<select name="parent_product_id">
				 		<option value="0">Select</option>
				 	   <?php
				 	     while($info2 = mysql_fetch_assoc($result2)){
				 	     	?>
				 	     	<option value="<?php echo $info2['ABProductId']; ?>"><?php echo $info2['Name']; ?></option>
				 	     	<?php
				 	     }
				 	   ?>	
				 	</select>
				 	<?php
				 }else{
				 	$query = 'SELECT Name FROM products WHERE ABProductId = "'.$pResult['ParentABProductId'].'"';
				 	$result2 = mysql_query($query);
				 	$info2 = mysql_fetch_assoc($result2);
				 	echo $info2['Name'];
				 }
				?>
				</td>
			</tr>
			<?php
			 }
			?>
			<tr>
				<td>Product Name</td>
				<td>:</td>
				<td><input type="text" maxlength="20" name="prod_name" value="<?php echo $productArr['Name']; ?>" /></td>
			</tr>
			<tr>
				<td>ANR</td>
				<td>:</td>
				<td><input type="text" maxlength="20" name="prod_anr" value="<?php echo $productArr['ANR']; ?>" /></td>
			</tr>
			<tr>
				<td>EAN</td>
				<td>:</td>
				<td><input type="text" maxlength="20" name="prod_ean" value="<?php echo $productArr['EAN']; ?>" /></td>
			</tr>
			<tr>
				<td>Short Desc</td>
				<td>:</td>
				<td><input type="text" maxlength="20" name="prod_short_desc" value="<?php echo $productArr['ShortDesc']; ?>" /></td>
			</tr>
			<tr>
				<td>Quantity</td>
				<td>:</td>
				<td><input type="text" maxlength="5" name="prod_quantity" value="<?php echo $productArr['Quantity']; ?>" /></td>
			</tr>
			<tr>
				<td colspan="3" style="text-align:center;">
					<a href="javascript:void(0)" onclick="postForm();">Save</a>
					<a href="<?php echo $appPath;?>/products/">Cancel</a>
				</td>
			</tr>
		</table>
	</form>
		</div>
	<td>
</tr>
<?php
require_once('../footer.php');
?>
<script type="text/javascript">
function postForm(){
	document.getElementById("frm1").submit();
}
</script>