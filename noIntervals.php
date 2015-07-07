<?php
require 'db/db_connect.php';
require 'functions.php';

$products       = getProductsBetweenInterval($_POST['settings:startdate'], $_POST['settings:finishdate'], $db);
$products       = mergeVariants($products);
$shipping_total = getShippingTotals($_POST['settings:startdate'], $_POST['settings:finishdate'], $db);
$coupon_total   = getCouponTotals($_POST['settings:startdate'], $_POST['settings:finishdate'], $db);

$eol = "\n";
// $eol = '<br>';

// var_dump($shipping_total);
// var_dump($coupon_total);

$products_price_total = $products_quantity_total = 0;

echo 'Product Code,Variant Code,Name,Quantity Sold,Revenue, Warranty Term' . $eol;

foreach ($products as $product) {

	echo '"' . $product['code'] . '","' . $product['variant_code'] . '","' . $product['name'] . '","' . $product['quantity'] . '","' . '$' . number_format($product['price'], 2) . '","' . getCustomFieldValue($product['product_id'], 1, $db) . '"' . $eol;

	$products_price_total += $product['price'];
	$products_quantity_total += $product['quantity'];

}

echo ',,,"$' . number_format($products_quantity_total, 2) . '","$' . number_format($products_price_total, 2) . '"' . $eol;
echo $eol . $eol;
echo 'Shipping,Total Shipping Revenue,,,"$' . number_format($shipping_total, 2) . '"' . $eol;
echo 'Coupon,Total Coupon Discounts,,,"($' . number_format($coupon_total, 2) . ')"' . $eol;