<?php
require '../db/dbConnect.php';
include '../includes/functions.php';

$products = getProductsBetweenInterval($_POST['settings:startdate'], $_POST['settings:finishdate'], $db);

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
        }

        tr:first-of-type td {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        td {
            padding: 10px;
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
            <td colspan="5"><h1>Pick List - SeagullBook.com</h1></td>
            <td><?php echo $DateTime->format('l, F d, Y');?></td>
        </tr>
        <tr>
            <td>Quantity</td>
            <td>SKU /Item Name</td>
            <td align="middle" colspan="2">Options 1 & 2</td>
            <td align="middle" colspan="2">Options 3 & 4</td>
        </tr>
        <?php foreach ($products as $product) { ?>
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
                <td valign="bottom" align="middle"><?php echo getCustomFieldValue($product['product_id'], 1, $db); ?></td>
                <td valign="bottom">QOH:</td>
                <td valign="bottom" align="middle">
                    <?php
                        if ($product['variant_id'] != 0) {
                            echo (getVariantBasketInventory($product['code'], $db, $product['variant_id']) + getInventory($product['variant_id'], $db));
                        } else {
                            echo (getBasketInventory($product['code'], $db, $product['variant_id']) + getInventory($product['product_id'], $db));
                        }
                    ?>
                </td>
            </tr>
            <tr class="target">
                <td colspan="2"></td>
                <td valign="top">Author:</td>
                <td align="middle"><?php echo getCustomFieldValue($product['product_id'], 2, $db); ?></td>
                <td valign="top">Section:</td>
                <td align="middle"><?php echo getCustomFieldValue($product['product_id'], 19, $db); ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>


