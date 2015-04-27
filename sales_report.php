<?php
date_default_timezone_set('America/Los_Angeles');
require 'db_connect.php';
include 'functions.php';
include 'IntervalMakerClass.php';

error_reporting(E_ALL);

$myIntervalMaker = new IntervalMaker();
$dates = $myIntervalMaker->createDates($_POST['settings:startdate'], $_POST['settings:finishdate'], $_POST['settings:desired_interval']);
$timestamps = $myIntervalMaker->createTimestamps($_POST['settings:startdate'], $_POST['settings:finishdate'], $_POST['settings:desired_interval']);

$first_wave = get_products_between_interval($_POST['settings:startdate'], $_POST['settings:finishdate'], $db);
$first_wave = merge_variants($first_wave);

$first_row[] = 'PRODUCT_CODE';
$first_row[] = 'VARIANT_CODE';
$first_row[] = 'PRODUCT_NAME';

// DEBUG
// echo '-------------- ENTIRE INTERVALS PRODUCTS --------------<BR>';
// var_dump($first_wave);

foreach ($first_wave as $key => $product) {
	$row = array();
	$row[] = $product['code'];
	$row[] = $product['variant_code'];
	$row[] = $product['name'];

	$match_found = false;

	foreach ($timestamps as $key2 => $timestamp) {
		$products = get_products_between_interval($timestamp[0], $timestamp[1], $db);
		$products = merge_variants($products);

		// DEBUG
		// echo '-------------- SUB-INTERVAL ' . $key2 . ' PRODUCTS --------------<BR>';
		// var_dump($products);

		if ($products !== null) {
			foreach ($products as $product_inside) {
				if ($product['variant_code'] != '' && $product['variant_code'] == $product_inside['variant_code'] || strpos($product['line_id'], $product_inside['line_id']) !== false) {
					$row[] = $product_inside['quantity'];
					$match_found = true;
				}
			}
			if ($match_found === false) {
				$row[] = '';
			}
			$products_null = false;
		} else {
			$row[] = '';
			$products_null = true;
		}

		if ($key == 0) {
			if (!($key2 == count($timestamps) - 1 && $products_null === true)) {
				$first_row[] = $dates[$key2];
			}
		}

	}
	$csv[] = $row;
}

foreach ($first_row as $key => $column) {
	if ($key !== count($first_row) - 1) {
		echo $column . ',';
	} else {
		echo $column . "\n";
	}
}
foreach ($csv as $key => $row) {
	foreach ($row as $key => $column) {
		if ($key !== count($row) - 1) {
			echo $column . ',';
		} else {
			echo $column . "\n";
		}
	}
}