<?php
require '../db/dbConnect.php';
include '../includes/functions.php';

$startdate = $_POST['settings:startdate'];
$finishdate = $_POST['settings:finishdate'];

$ProductData = new GetProductData;
$ProductDataMerger = new MergeProductData;
$StoreData = new GetStoreData;

$Date = new DateTime;

$StartDate = new DateTime;
$StartDate = $StartDate->setTimestamp($startdate);

$FinishDate = new DateTime;
$FinishDate = $FinishDate->setTimestamp($finishdate);

$shippingTotals = $StoreData->getShippingTotals($startdate, $finishdate, $db);
$couponTotals = $StoreData->getCouponTotals($startdate, $finishdate, $db);


$allProducts = $ProductData->getProductsBetweenInterval($startdate, $finishdate, $db);


$allVariants = $ProductData->getVariantProductsBetweenInterval($startdate, $finishdate, $db);
$allVariants = $ProductDataMerger->mergeVariantParts($allVariants);
$allVariants = $ProductData->getVariantCodes($allVariants, $db);
$allVariants = $ProductDataMerger->mergeVariants($allVariants);


$allNonVariants = $ProductData->getNonVariantProducts($allProducts, $allVariants);
$allNonVariants = $ProductDataMerger->mergeNonVariantProducts($allNonVariants);


$allProducts = array_merge($allVariants, $allNonVariants);

$quantity_total = $revenue_total = 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Miva Merchant Store Sales Report</title>

    <style>
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        td {
            padding: 10px;
        }

    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="3" style="border-bottom: 1px solid black;">
                <b>Miva Merchant Store - Sales by Product</b>
            </td>
            <td align="right" style="border-bottom: 1px solid black;">
                <?php echo $Date->format('l, F d, Y'); ?>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <?php echo $StartDate->format('d-F-Y G:i:s') . ' thru ' . $FinishDate->format('d-F-Y G:i:s'); ?>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td style="border-bottom: 1px solid black;">SKU</td>
            <td style="border-bottom: 1px solid black;">Name</td>
            <td style="border-bottom: 1px solid black;">Quantity Sold</td>
            <td style="border-bottom: 1px solid black;">Revenue</td>
        </tr>
        <?php
            foreach ($allProducts as $key => $product) {
                
                $revenue_total += $product['total_revenue'];
                $quantity_total += $product['quantity'];

        ?>
            <tr>
                <td><?php echo $product['code']; ?></td>
                <td><?php echo ( $product['variant_name'] ? $product['variant_name'] : $product['name'] ); ?></td>
                <td align="middle"><?php echo $product['quantity']; ?></td>
                <td align="middle"><?php echo '$' . number_format($product['total_revenue'], 2); ?></td>
            </tr>
        <?php
            }
        ?>
        <tr>
            <td colspan="2"></td>
            <td align="middle" style="border-top: 1px solid black;">
                <?php echo $quantity_total; ?>
            </td>
            <td align="middle" style="border-top: 1px solid black;">
                <?php echo '$' . number_format($revenue_total, 2); ?>
            </td>
        </tr>
        <tr>
            <td>
                <b>Shipping</b>
            </td>
            <td colspan="2">
                <b>Total Shipping Revenue</b>
            </td>
            <td align="middle">
                <b><?php echo '$' . number_format(abs($shippingTotals), 2); ?></b>
            </td>
        </tr>
        <tr>
            <td>
                <b>Coupon</b>
            </td>
            <td colspan="2">
                <b>Total Coupon Discounts</b>
            </td>
            <td align="middle">
                <b><?php echo '($' . number_format(abs($couponTotals), 2) . ')'; ?></b>
            </td>
        </tr>
    </table>
</body>
</html>