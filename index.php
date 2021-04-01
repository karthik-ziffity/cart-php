<?php
session_start();
require_once("dbcontroller.php");
$db_handle = new DBController();
if(!empty($_GET["action"])) {
switch($_GET["action"]) {
	case "add":
		if(!empty($_POST["quantity"])) {
			$productByCode = $db_handle->runQuery("SELECT * FROM tblproduct WHERE code='" . $_GET["code"] . "'");
			$itemArray = array($productByCode[0]["code"]=>array('name'=>$productByCode[0]["name"], 'code'=>$productByCode[0]["code"], 'quantity'=>$_POST["quantity"], 'price'=>$productByCode[0]["price"], 'image'=>$productByCode[0]["image"]));
			
			if(!empty($_SESSION["cart_item"])) {
				if(in_array($productByCode[0]["code"],array_keys($_SESSION["cart_item"]))) {
					foreach($_SESSION["cart_item"] as $k => $v) {
							if($productByCode[0]["code"] == $k) {
								if(empty($_SESSION["cart_item"][$k]["quantity"])) {
									$_SESSION["cart_item"][$k]["quantity"] = 0;
								}
								$_SESSION["cart_item"][$k]["quantity"] += $_POST["quantity"];
							}
					}
				} else {
					$_SESSION["cart_item"] = array_merge($_SESSION["cart_item"],$itemArray);
				}
			} else {
				$_SESSION["cart_item"] = $itemArray;
			}
		}
	break;
	case "remove":
		if(!empty($_SESSION["cart_item"])) {
			foreach($_SESSION["cart_item"] as $k => $v) {
					if($_GET["code"] == $k)
						unset($_SESSION["cart_item"][$k]);				
					if(empty($_SESSION["cart_item"]))
						unset($_SESSION["cart_item"]);
			}
		}
	break;
	case "empty":
		unset($_SESSION["cart_item"]);
	break;	
}
}
?>
<HTML>
<HEAD>
<TITLE>Product listing</TITLE>
<link href="style.css" type="text/css" rel="stylesheet" />
</HEAD>
<BODY>
	<h1 class="header-main">Product Listing</h1>
<div id="shopping-cart">
<div class="txt-heading">Shopping Cart</div>

<a id="btnEmpty" href="index.php?action=empty">Empty Cart</a>
<?php
if(isset($_SESSION["cart_item"])){
    $total_quantity = 0;
    $total_price = 0;
?>	
<table class="tbl-cart" cellpadding="10" cellspacing="1">
<tbody>
<tr>
	<th style="text-align:left;">Name</th>
	<th style="text-align:left;">Code</th>
	<th style="text-align:right;" width="5%">Quantity</th>
	<th style="text-align:right;" width="10%">Unit Price</th>
	<th style="text-align:right;" width="10%">Price</th>
	<th style="text-align:center;" width="5%">Remove</th>
</tr>	
<?php		
    foreach ($_SESSION["cart_item"] as $item){
        $item_price = $item["quantity"]*$item["price"];
		?>
				<tr>
				<td><img src="<?php echo $item["image"]; ?>" class="cart-item-image" /><?php echo $item["name"]; ?></td>
				<td><?php echo $item["code"]; ?></td>
				<td style="text-align:right;"><?php echo $item["quantity"]; ?></td>
				<td  style="text-align:right;"><?php echo "$ ".$item["price"]; ?></td>
				<td  style="text-align:right;"><?php echo "$ ". number_format($item_price,2); ?></td>
				<td style="text-align:center;"><a href="index.php?action=remove&code=<?php echo $item["code"]; ?>" class="btnRemoveAction"><img src="icon-delete.png" alt="Remove Item" /></a></td>
				</tr>
				<?php
				$total_quantity += $item["quantity"];
				$total_price += ($item["price"]*$item["quantity"]);
		}
		?>

<tr>
<td colspan="2" align="right">Total:</td>
<td align="right"><?php echo $total_quantity; ?></td>
<td align="right" colspan="2"><strong><?php echo "$ ".number_format($total_price, 2); ?></strong></td>
<td></td>
</tr>
</tbody>
</table>		
  <?php
} else {
?>
<div class="no-records">Your Cart is Empty</div>
<?php 
}
?>
</div>

<div id="product-grid">
	<div class="txt-heading">Products</div>
	<?php
	$product_array = $db_handle->runQuery("SELECT * FROM tblproduct ORDER BY id ASC");

	$results_per_page = 8; 
	$number_of_result = sizeof($product_array);
	
	//$number_of_result = mysqli_num_rows($product_array); 
	$number_of_page = ceil ($number_of_result / $results_per_page);  
	
	if (!isset ($_GET['page']) ) {  
		$page = 1;  
	} else {  
		$page = $_GET['page'];  
	}  
	
	$page_first_result = ($page-1) * $results_per_page;   
	$product_array = $db_handle->runQuery("SELECT * FROM tblproduct LIMIT " . $page_first_result . ',' . $results_per_page);  
	echo '<div class="page">';
	echo "pagination:";
	
	for($page = 1; $page<= $number_of_page; $page++) {  
		
		echo '<a class="btn" href = "/ct/index.php?page=' . $page . '">' . $page . ' </a>';  
	}
	echo '</div>'
	
	?>

	<!--  -->

	<ul class="product-category">
		<?php

		$product_unique = array_unique($product_array);
		echo $product_array;
		if (!empty($product_unique)) { 
		foreach($product_unique as $key=>$value) {
		?>
		
			<li><a><?php echo $product_unique[$key]["category"]; ?></a></li>
		
		<?php
			}
		}
		?>
	</ul>


<!--  -->


	<?php
	if (!empty($product_array)) { 
	foreach($product_array as $key=>$value) {
	?>
	<div class="product-item">
		<form method="post" action="index.php?action=add&code=<?php echo $product_array[$key]["code"]; ?>">
		<div class="product-image"><img src="<?php echo $product_array[$key]["image"]; ?>"></div>
		<div class="product-tile-footer">
		<div class="product-title"><?php echo $product_array[$key]["name"]; ?></div>
		<div class="product-price"><?php echo "$".$product_array[$key]["price"]; ?></div>
		<div class="cart-action"><input type="text" class="product-quantity" name="quantity" value="1" size="2" /><input type="submit" value="Add to Cart" class="btnAddAction" /></div>
		</div>
		</form>
	</div>
	<?php
		}
	}
	?>
</div>
</BODY>
</HTML>