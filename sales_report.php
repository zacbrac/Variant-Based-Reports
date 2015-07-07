<?php
require 'db/db_connect.php';
include 'functions.php';

$products = get_products_between_interval($_POST['settings:startdate'], $_POST['settings:finishdate'], $db);
$products = merge_variants($products);
$shipping_total = get_shipping_totals($_POST['settings:startdate'],$_POST['settings:finishdate'], $db);
$coupon_total = get_coupon_totals($_POST['settings:startdate'],$_POST['settings:finishdate'], $db);

// var_dump($shipping_total);
// var_dump($coupon_total);
$products_price_total = $products_quantity_total = 0;

echo 'Product Code,Variant Code,Name,Quantity Sold,Revenue' . "\n";
foreach ($products as $product) {
    echo $product['code'] . ',' . $product['variant_code'] . ',' . $product['name'] . ',' . $product['quantity'] . ',' . '$' . number_format($product['price'], 2) . "\n";
    $products_price_total += $product['price'];
    $products_quantity_total += $product['quantity'];
}
echo ',,,$' . number_format($products_quantity_total,2) . ',$' . number_format($products_price_total,2) . "\n";
echo "\n\n";
echo 'Shipping,Total Shipping Revenue,,,$' . number_format($shipping_total, 2) . "\n";
echo 'Coupon,Total Coupon Discounts,,,($' . number_format($coupon_total, 2) . ")\n";