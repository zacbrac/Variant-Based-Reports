<?php
require '../db/dbConnect.php';
include '../includes/functions.php';
include '../includes/IntervalMaker.php';

$StartDate = new DateTime();
$StartDate->setTimestamp($_POST['settings:startdate']);

$FinishDate = new DateTime();
$FinishDate->setTimestamp($_POST['settings:finishdate']);

switch ($_POST['settings:desired_interval']) {
    case 'hours':
        $interval = 'P1H';
        break;
    case 'days':
        $interval = 'P1D';
        break;
    case 'weeks':
        $interval = 'P1W';
        break;
    case 'months':
        $interval = 'P1M';
        break;
    case 'years':
        $interval = 'P1Y';
        break;
    default:
        $interval = null;
}

$MyIntervalMaker = new IntervalMaker();
$times           = $MyIntervalMaker->createDates($StartDate, $FinishDate, new DateInterval($interval));

$timestamps_count = count($times);

$csv = '';

$ProductData = new GetProductData;
$ProductDataMerger = new MergeProductData;
$StoreData = new GetStoreData;

$first_wave = $ProductData->getProductsBetweenInterval($_POST['settings:startdate'], $_POST['settings:finishdate'], $db);

$allVariants = $ProductData->getVariantProductsBetweenInterval($startdate, $finishdate, $db);
$allVariants = $ProductDataMerger->mergeVariantParts($allVariants);
$allVariants = $ProductData->getVariantCodes($allVariants, $db);
$allVariants = $ProductDataMerger->mergeVariants($allVariants);


$allNonVariants = $ProductData->getNonVariantProducts($first_wave, $allVariants);
$allNonVariants = $ProductDataMerger->mergeNonVariantProducts($allNonVariants);


$first_wave = array_merge($allVariants, $allNonVariants);


$first_row = 'PRODUCT_CODE,VARIANT_CODE,PRODUCT_NAME,';

foreach ($first_wave as $key => $product) {

    $row = '"' . $product['code'] . '","' . $product['variant_code'] . '","' . $product['name'] . '",';

    $match_found = false;

    foreach ($times as $key2 => $time) {

        $products = $ProductData->getProductsBetweenInterval($time[0]->getTimestamp(), $time[1]->getTimestamp(), $db);


        $allVariants = $ProductData->getVariantProductsBetweenInterval($startdate, $finishdate, $db);
        $allVariants = $ProductDataMerger->mergeVariantParts($allVariants);
        $allVariants = $ProductData->getVariantCodes($allVariants, $db);
        $allVariants = $ProductDataMerger->mergeVariants($allVariants);


        $allNonVariants = $ProductData->getNonVariantProducts($products, $allVariants);
        $allNonVariants = $ProductDataMerger->mergeNonVariantProducts($allNonVariants);


        $products = array_merge($allVariants, $allNonVariants);

        if ($products !== null) {

            foreach ($products as $product_inside) {

                if (($product['variant_code'] != '' && $product['variant_code'] == $product_inside['variant_code']) || ($product_inside['attr_id'] == '' && $product_inside['attr_code'] == '' && $product_inside['option_id'] == '' && $product_inside['variant_code'] == '' && $product_inside['product_id'] == $product['product_id']) || (strpos($product['line_id'], $product_inside['line_id']) !== false)) {

                    $row .= $product_inside['quantity'] . ',';
                    $match_found = true;

                }

            }

            if ($match_found === false) {

                $row .= '' . ',';

            }

            $products_null = false;

        } else {

            $row .= '' . ',';
            $products_null = true;

        }

        if ($key == 0) {

            if (!($key2 == $timestamps_count - 1 && $products_null === true)) {

                $first_row .= $times[$key2][0]->format('m/d/Y') . ',';

            }

        }

    }

    $csv .= $row . "\n";

}

echo $first_row . "\n";
echo $csv;