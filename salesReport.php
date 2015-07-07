<?php
require 'db/dbConnect.php';
require 'functions.php';

$products       = getProductsBetweenInterval($_POST['settings:startdate'], $_POST['settings:finishdate'], $db);
$products       = mergeVariants($products);

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
            <td colspan="2">Quantity</td>
            <td>SKU /Item Name</td>
            <td>Options 1 & 2</td>
            <td>Options 3 & 4</td>
            <td>Options 5 & 6</td>
        </tr>
        <?php foreach ($products as $product) { ?>
            <tr>
                <td align="middle"><?php echo $product['quantity'];?></td>
                <td>
                    <span class="sku">
                        <?php echo $product['product_id'];?>
                    </span>                    
                    <span class="name">
                        <?php echo $product['name'];?>
                    </span>
                    <small>Notes</small>
                </td>
                <td valign="middle">Format:</td>
                <td>CD</td>
                <td valign="middle">QOH:</td>
                <td><?php echo $product['quantity']; ?></td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td valign="middle">Author:</td>
                <td align="middle">Someone</td>
                <td valign="middle">Section:</td>
                <td align="middle">Books</td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>


