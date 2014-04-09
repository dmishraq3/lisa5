<?php
require_once('../config.php');
require_once('../header.php');
?>
<tr>
	<td >
		<div style="min-height:445px;border:0px solid red;">
		<table style="width:100%;border-collapse:collapse" border="1">
			<tr><td colspan="10" align="right">
				<a href="<?php echo $appPath; ?>/products/mng-product.php">Add New Product</a>
			</td></tr>
			<tr>
				<td colspan="10">
					<?php
					 if(isset($_SESSION['PUSH_STATUS'])){
					 	echo $_SESSION['PUSH_STATUS'];
					 	unset($_SESSION['PUSH_STATUS']);
					 }
					?>
				</td>
			</tr>
			<tr bgcolor="#912A2A" style="color:#FFFFFF;">
				<td>Id</td>
				<td>AB Prod.Id</td>
				<td>ANR</td>
				<td>EAN</td>
				<td>Name</td>
				<td>Quantity</td>
				<td>In Stock</td>
				<td>Type</td>
				<td>Push</td>
				<td>Edit</td>
			</tr>
			<?php
			 $query = 'SELECT * FROM products ORDER BY ProductId';
			 $results = mysql_query($query,$dbConn);
			 $rowFound = FALSE;
			 while($row = mysql_fetch_assoc($results)){
			 	$rowFound = TRUE;
			 	if(empty($row['ABProductId'])){
			 		$row['ABProductId'] = '---';
			 	}
			 	$push = 'No';
			 	if($row['UpdatedLocally'] > $row['UpdatedAB']){
			 		$push = '<a href="'.$appPath.'/products/push-data.php?id='.$row['ProductId'].'">Push</a>';
			 	}
			 	
			 	if($row['Discontinued'] == '1'){
			 		$row['Discontinued'] = 'Yes';
			 	}else{
			 		$row['Discontinued'] = 'No';	
			 	}
			 	
			 	if($row['ProductType'] == '1'){
			 		$row['ProductType'] = 'Variation';
			 	}else{
			 		$row['ProductType'] = 'Standard';	
			 	}
			 	$editLink = '<a href="'.$appPath.'/products/mng-product.php?id='.$row['ProductId'].'">Edit</a>';
			 	
			 	$tableString .= '
			 	<tr>
			 	<td>'.$row["ProductId"].'</td>
			 	<td>'.$row["ABProductId"].'</td>
			 	<td>'.$row["ANR"].'</td>
			 	<td>'.$row["EAN"].'</td>
			 	<td>'.$row["Name"].'</td>
			 	<td>'.$row["Quantity"].'</td>
			 	<td>'.$row["InStock"].'</td>
			 	<td>'.$row["ProductType"].'</td>
			 	<td>'.$push.'</td>
			 	<td>'.$editLink.'</td>
			 	</tr>';
			 }
			 if($rowFound){
			 	echo $tableString;
			 }else{
			 	echo '<tr><td colspan="10" align="center"><strong>No Products Found </strong></td></tr>';
			 }
			?>
		</table>
		</div>
	<td>
</tr>

<?php
require_once('../footer.php');
?>