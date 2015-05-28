<?php
function merge_variant_parts($products) {
	$count = count($products) - 1;
	for ($i = 0; $i < $count; $i++) {
		if ($products[$i + 1]['line_id'] == $products[$i]['line_id']) {
			$products[$i + 1]['attr_id'] = $products[$i]['attr_id'] . ',' . $products[$i + 1]['attr_id'];
			$products[$i + 1]['option_id'] = $products[$i]['option_id'] . ',' . $products[$i + 1]['option_id'];
			unset($products[$i]);
		}
	}
	return array_values($products);
}

function get_variant_codes($all_products_merged, PDO $db) {
	//Go through each product and assign variant_code
	foreach ($all_products_merged as &$product) {
		if ($product['option_id'] != '') {
			$queries = array();
			$option_ids = explode(',', $product['option_id']);
			$attr_ids = explode(',', $product['attr_id']);
			if (count($option_ids) == count($attr_ids)) {
				for ($i = 0; $i < count($option_ids); $i++) {
					$queries[] = '(s01_ProductVariants.option_id = ' . $option_ids[$i] . ' AND s01_ProductVariants.attr_id = ' . $attr_ids[$i] . ')';
				}
				$count_less_1 = count($option_ids) - 1;
				$rest_of_query = implode(' OR ', $queries) . ' GROUP BY s01_ProductVariants.variant_id HAVING COUNT(*) > ' . $count_less_1;
			} else {
				$rest_of_query = implode(' OR ', $queries);
			}
			$variant_code = $db->prepare(
				'SELECT s01_Products.code
				FROM s01_ProductVariants
				INNER JOIN s01_ProductVariantParts
				ON s01_ProductVariantParts.variant_id=s01_ProductVariants.variant_id
				INNER JOIN s01_Products
				ON s01_Products.id=s01_ProductVariantParts.part_id
				WHERE ' . $rest_of_query);
			$variant_code->execute();
			$result = $variant_code->fetch(PDO::FETCH_ASSOC);
			$product['variant_code'] = $result['code'];
		}
	}
	return $all_products_merged;
}

function get_non_variant_products($all_products, $all_variant_products) {
	$count = count($all_products) - 1;
	$second_count = count($all_variant_products) - 1;
	for ($i = 0; $i <= $count; $i++) {
		for ($j = 0; $j <= $second_count; $j++) {
			if ($all_products[$i]['line_id'] == $all_variant_products[$j]['line_id']) {
				$captured[] = $i;
			}
		}
	}
	foreach ($captured as $value) {
		unset($all_products[$value]);
	}
	$all_products = array_values($all_products);
	$all_products = add_blank_variant_codes($all_products);
	$all_products = merge_non_variant_products($all_products);
	return $all_products;
}

function add_blank_variant_codes($non_variant_products) {
	foreach ($non_variant_products as &$product) {
		$product['attr_id'] = '';
		$product['attr_code'] = '';
		$product['option_id'] = '';
		$product['variant_code'] = '';
	}
	return $non_variant_products;
}

function merge_non_variant_products(&$non_variant_products) {
	$count = count($non_variant_products) - 1;
	for ($i = 0; $i < $count; $i++) {
		$done = false;
		for ($j = $i + 1; $j < $count + 1; $j++) {
			if ($non_variant_products[$i]['product_id'] == $non_variant_products[$j]['product_id']) {
				$non_variant_products[$j]['quantity'] = $non_variant_products[$i]['quantity'] + $non_variant_products[$j]['quantity'];
				$non_variant_products[$j]['line_id'] = $non_variant_products[$i]['line_id'] . ',' . $non_variant_products[$j]['line_id'];
				unset($non_variant_products[$i]);
				$done = true;
				break;
			}
		}
		if ($done) {
			continue;
		}
	}
	return array_values($non_variant_products);
}

function get_variant_products($startdate, $finishdate, PDO $db) {
	$variant_products = $db->prepare(
		"SELECT s01_Orders.orderdate,
				s01_OrderItems.line_id,
				s01_OrderItems.product_id,
				s01_OrderItems.code,
				s01_OrderItems.name,
				s01_OrderItems.quantity,
				s01_OrderOptions.attr_id,
				s01_OrderOptions.attr_code,
				s01_OrderOptions.option_id
				FROM s01_Orders
				INNER JOIN s01_OrderItems
				ON s01_OrderItems.order_id=s01_Orders.id
				INNER JOIN s01_OrderOptions
				ON s01_OrderOptions.line_id=s01_OrderItems.line_id
				WHERE s01_Orders.orderdate >= $startdate AND s01_Orders.orderdate <= $finishdate"
	);
	$variant_products->execute();
	while ($row = $variant_products->fetch(PDO::FETCH_ASSOC)) {
		$all_variant_products[] = $row;
	}
	return $all_variant_products;
}

function merge_variants(&$variant_products) {
	$count = count($variant_products) - 1;
	for ($i = 0; $i < $count; $i++) {
		$done = false;
		for ($j = $i + 1; $j < $count + 1; $j++) {
			if ($variant_products[$i]['variant_code'] == $variant_products[$j]['variant_code'] && $variant_products[$i]['variant_code'] !== '') {
				$variant_products[$j]['quantity'] = $variant_products[$i]['quantity'] + $variant_products[$j]['quantity'];
				unset($variant_products[$i]);
				$done = true;
				break;
			}
		}
		if ($done) {
			continue;
		}
	}
	return array_values($variant_products);
}
function filter_non_variants($all_products_merged) {
	$count = count($all_products_merged) - 1;
	for ($i = 0; $i < $count; $i++) {
		$done = false;
		for ($j = $i + 1; $j < $count + 1; $j++) {
			if ($all_products_merged[$i]['variant_code'] == '' && $all_products_merged[$j]['variant_code'] == '' && $all_products_merged[$i]['product_id'] == $all_products_merged[$j]['product_id']) {
				$all_products_merged[$j]['quantity'] = $all_products_merged[$i]['quantity'] + $all_products_merged[$j]['quantity'];
				unset($all_products_merged[$i]);
				$done = true;
				break;
			}
		}
		if ($done) {
			continue;
		}
	}
	return array_values($all_products_merged);
}

function get_products_between_interval($startdate, $finishdate, PDO $db) {
	$startdate = (int) $startdate;
	$finishdate = (int) $finishdate;
	$all_products = $db->prepare(
		"SELECT s01_Orders.orderdate,
				s01_OrderItems.line_id,
				s01_OrderItems.product_id,
				s01_OrderItems.code,
				s01_OrderItems.name,
				s01_OrderItems.quantity
				FROM s01_Orders
				INNER JOIN s01_OrderItems
				ON s01_OrderItems.order_id=s01_Orders.id
				WHERE s01_Orders.orderdate >= $startdate AND s01_Orders.orderdate <= $finishdate"
	);
	$all_products->execute();
	while ($row = $all_products->fetch(PDO::FETCH_ASSOC)) {
		$allproducts[] = $row;
	}

	//ALL VARIANT PRODUCTS IN THIS TIME INTERVAL
	$all_variant_products = get_variant_products($startdate, $finishdate, $db);
	//ALL NON-VARIANT PRODUCTS IN THIS TIME INTERVAL
	$non_variants = get_non_variant_products($allproducts, $all_variant_products);
	//ALL PRODUCTS MERGED AFTER INDIVIDUAL MERGING PROCESS HAS BEEN COMPLETED
	$merged_variants = merge_variant_parts($all_variant_products);

	//CONTAINS ALL PRODUCTS FROM TIME RANGE
	if ($merged_variants !== null) {
		$all_products_merged = array_merge($non_variants, $merged_variants);
		//GET VARIANT CODES
		$all_products_merged = get_variant_codes($all_products_merged, $db);
		$all_products_merged = filter_non_variants($all_products_merged);
		return $all_products_merged;
	} else {
		return $non_variants;
	}

}
