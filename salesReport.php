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

$DateTime = new DateTime();

// echo ',,,"$' . number_format($products_quantity_total, 2) . '","$' . number_format($products_price_total, 2) . '"' . $eol;
// echo $eol . $eol;
// echo 'Shipping,Total Shipping Revenue,,,"$' . number_format($shipping_total, 2) . '"' . $eol;
// echo 'Coupon,Total Coupon Discounts,,,"($' . number_format($coupon_total, 2) . ')"' . $eol;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shipment Picklist</title>
</head>
<body>
    <table>
        <tr>
            <td colspan="5"><h1>Pick List - SeagullBook.com</h1></td>
            <td><?php echo $DateTime->format('l, F d, Y');?></td>
        </tr>
        <tr><td colspan="6"></td></tr>
        <tr>
            <td>Product Code</td>
            <td>Variant Code</td>
            <td>Name</td>
            <td>Quantity Sold</td>
            <td>Revenue</td>
            <td>Warranty Term</td>
        </tr>
        <?php
foreach ($products as $product) {
	$products_price_total += $product['price'];
	$products_quantity_total += $product['quantity'];
	?>
            <tr>
                <td><?php echo $product['code'];?></td>
                <td><?php echo $product['variant_code'];?></td>
                <td><?php echo $product['name'];?></td>
                <td><?php echo $product['quantity'];?></td>
                <td><?php echo '$' . number_format($product['price'], 2);?></td>
                <td><?php echo getCustomFieldValue($product['product_id'], 1, $db);?></td>
            </tr>
        <?php }
?>
        <tr>

        </tr>
    </table>
</body>
</html>


