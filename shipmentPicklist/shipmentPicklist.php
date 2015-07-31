<?php
require '../db/dbConnect.php';
include '../includes/functions.php';

$ProductData = new GetProductData;
$ProductDataMerger = new MergeProductData;
$StoreData = new GetStoreData;


$startdate = $_POST['settings:startdate'];
$finishdate = $_POST['settings:finishdate'];


$products = $ProductData->getProductsBetweenInterval($startdate, $finishdate, $db);


$allVariants = $ProductData->getVariantProductsBetweenInterval($startdate, $finishdate, $db);
$allVariants = $ProductDataMerger->mergeVariantParts($allVariants);
$allVariants = $ProductData->getVariantCodes($allVariants, $db);
$allVariants = $ProductDataMerger->mergeVariants($allVariants);


$allNonVariants = $ProductData->getNonVariantProducts($products, $allVariants);
$allNonVariants = $ProductDataMerger->mergeNonVariantProducts($allNonVariants);


$products = array_merge($allVariants, $allNonVariants);

//Sorts products based on variant_code
$productNames = array();

foreach ($products as $key => $product) {
    
    if ($product['variant_code'] != '') {
    
        $productNames[$key] = $product['variant_code'];

    } else {
        
        $productNames[$key] = $product['code'];
    
    }

}

array_multisort($productNames, SORT_ASC, $products);

$DateTime = new DateTime();
$date = $DateTime->format('l, F d, Y');

// used in page breaking checks
$next_target = 3;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        
        body {
            font-size: .9em;
        }

        table {
            border-collapse: collapse;
            page-break-inside:auto;
            width: 100%;
        }

        @media all {
            .page-break { display: none; }
        }

        @media print {
            .page-break { display: block; page-break-before: always; }
        }

        table:first-of-type tr:first-of-type td {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        td {
            padding: 10px;
        }

        tr { 
            page-break-inside:avoid; 
            page-break-after:auto 
        }

        span.sku, span.name {
            display: block;
            font-weight: bold;
        }

        small {
            display: block;
            text-align: center;
        }

        .target td {
            border-bottom: 1px solid #000;
        }

        .checkbox {
            width: 50px;
            height: 50px;
            display: block;
            margin: 0 auto;
            border: 1px solid #000;
        }

    </style>
    <title>Shipment Picklist</title>
</head>
<body>
    <table>
        <tr>
            <td colspan="5"><h1>Pick List - Miva Merchant Store</h1></td>
            <td><?php echo $date; ?></td>
        </tr>
        <tr>
            <td>Quantity</td>
            <td>SKU /Item Name</td>
            <td align="middle" colspan="2">Options 1 & 2</td>
            <td align="middle" colspan="2">Options 3 & 4</td>
        </tr>
        <?php foreach ($products as $key => $product) { ?>
            <tr>
                <td align="middle">
                    <?php echo $product['quantity'];?>
                    <div class="checkbox"></div>
                </td>
                <td>
                    <span class="sku">
                        <?php 
                            if ($product['variant_code'] != '') {
                                echo $product['variant_code'];
                            } else {
                                echo $product['code'];
                            }                            
                        ?>
                    </span>
                    <span class="name">
                        <?php 
                            if ($product['variant_name'] != '') {
                                echo $product['variant_name'];
                            } else {
                                echo $product['name'];
                            }
                        ?>
                    </span>
                    <small>Notes</small>
                </td>
                <td valign="bottom">Format:</td>
                <td valign="bottom" align="middle"><?php echo $ProductData->getProductCustomFieldValue($product['product_id'], 1, $db); ?></td>
                <td valign="bottom">QOH:</td>
                <td valign="bottom" align="middle">
                    <?php
                        if ($product['variant_id'] != 0) {
                            echo ($ProductData->getVariantBasketInventory($product['code'], $db, $product['variant_id']) + $ProductData->getInventory($product['variant_id'], $db));
                        } else {
                            echo ($ProductData->getBasketInventory($product['code'], $db, $product['variant_id']) + $ProductData->getInventory($product['product_id'], $db));
                        }
                    ?>
                </td>
            </tr>
            <tr class="target">
                <td colspan="2"></td>
                <td valign="top">Author:</td>
                <td align="middle"><?php echo $ProductData->getProductCustomFieldValue($product['product_id'], 2, $db); ?></td>
                <td valign="top">Section:</td>
                <td align="middle"><?php echo $ProductData->getProductCustomFieldValue($product['product_id'], 19, $db); ?></td>
            </tr>
            <?php  
                if ($key == $next_target) {
                    
                    $next_target += 5;
                    echo '</table><div class="page-break"></div><table>';

                }
            }
            ?>
    </table>
</body>
</html>
