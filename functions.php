<?php

function mergeVariantParts($products) {

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

function getVariantCodes($all_products_merged, PDO $db) {

    //Go through each product and assign variant_code
    foreach ($all_products_merged as &$product) {

        if ($product['option_id'] != '') {

            $queries = array();
            $option_ids = explode(',', $product['option_id']);
            $attr_ids = explode(',', $product['attr_id']);

            $count = count($option_ids);

            if (count($option_ids) == count($attr_ids)) {

                for ($i = 0; $i < $count; $i++) {
                    $queries[] = '(s01_ProductVariants.option_id = ' . $option_ids[$i] . ' AND s01_ProductVariants.attr_id = ' . $attr_ids[$i] . ')';
                }

                $count_less_1 = count($option_ids) - 1;
                $rest_of_query = implode(' OR ', $queries) . ' GROUP BY s01_ProductVariants.variant_id HAVING COUNT(*) > ' . $count_less_1;

            } else {

                $rest_of_query = implode(' OR ', $queries);

            }

            $variant_code = $db->prepare(
                'SELECT s01_Products.code,
                        s01_ProductVariants.variant_id
                FROM s01_ProductVariants
                INNER JOIN s01_ProductVariantParts
                ON s01_ProductVariantParts.variant_id=s01_ProductVariants.variant_id
                INNER JOIN s01_Products
                ON s01_Products.id=s01_ProductVariantParts.part_id
                WHERE ' . $rest_of_query);

            $variant_code->execute();

            $result = $variant_code->fetch(PDO::FETCH_ASSOC);

            $product['variant_code'] = $result['code'];
            $product['variant_id'] = $result['variant_id'];

        }

    }

    return $all_products_merged;

}

function getNonVariantProducts($all_products, $all_variant_products) {

    $captured = array();
    $count = count($all_products);
    $second_count = count($all_variant_products);

    for ($i = 0; $i < $count; $i++) {
        for ($j = 0; $j < $second_count; $j++) {
            if ($all_products[$i]['line_id'] == $all_variant_products[$j]['line_id']) {
                $captured[] = $i;
            }
        }
    }

    foreach ($captured as $value) {
        unset($all_products[$value]);
    }

    $all_products = array_values($all_products);
    $all_products = addBlankVariantCodes($all_products);
    $all_products = mergeNonVariantProducts($all_products);

    return $all_products;

}

function addBlankVariantCodes($non_variant_products) {

    foreach ($non_variant_products as &$product) {
        $product['attr_id'] = '';
        $product['attr_code'] = '';
        $product['option_id'] = '';
        $product['variant_code'] = '';
        $product['variant_id'] = '';
    }

    return $non_variant_products;

}

function mergeNonVariantProducts(&$non_variant_products) {

    $count = count($non_variant_products);

    for ($i = 0; $i < $count; $i++) {

        $done = false;

        for ($j = $i + 1; $j < $count; $j++) {

            if ($non_variant_products[$i]['product_id'] == $non_variant_products[$j]['product_id']) {
                $non_variant_products[$j]['quantity'] = $non_variant_products[$i]['quantity'] + $non_variant_products[$j]['quantity'];
                $non_variant_products[$j]['price'] = $non_variant_products[$i]['price'] + $non_variant_products[$j]['price'];
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

function getVariantProducts($startdate, $finishdate, PDO $db) {

    $variant_products = $db->prepare(
        'SELECT s01_Orders.orderdate,
                s01_OrderItems.line_id,
                s01_OrderItems.product_id,
                s01_OrderItems.code,
                s01_OrderItems.name,
                s01_OrderItems.price,
                s01_OrderItems.quantity,
                s01_OrderOptions.attr_id,
                s01_OrderOptions.attr_code,
                s01_OrderOptions.option_id
        FROM s01_Orders
        INNER JOIN s01_OrderItems
        ON s01_OrderItems.order_id=s01_Orders.id
        INNER JOIN s01_OrderOptions
        ON s01_OrderOptions.line_id=s01_OrderItems.line_id
        WHERE s01_Orders.orderdate >= :startdate AND s01_Orders.orderdate <= :finishdate'
    );

    $variant_products->execute(array(':startdate' => $startdate, ':finishdate' => $finishdate));

    $all_variant_products = $variant_products->fetchAll(PDO::FETCH_ASSOC);

    return $all_variant_products;
}

function mergeVariants(&$variant_products) {

    $count = count($variant_products);

    for ($i = 0; $i < $count; $i++) {

        $done = false;

        for ($j = $i + 1; $j < $count; $j++) {

            if ($variant_products[$i]['variant_code'] == $variant_products[$j]['variant_code'] && $variant_products[$i]['variant_code'] !== '') {

                $variant_products[$j]['quantity'] = $variant_products[$i]['quantity'] + $variant_products[$j]['quantity'];
                $variant_products[$j]['price'] = $variant_products[$i]['price'] + $variant_products[$j]['price'];

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

function filterNonVariants($all_products_merged) {

    $count = count($all_products_merged);

    for ($i = 0; $i < $count; $i++) {

        $done = false;

        for ($j = $i + 1; $j < $count; $j++) {

            if ($all_products_merged[$i]['variant_code'] == '' && $all_products_merged[$j]['variant_code'] == '' && $all_products_merged[$i]['product_id'] == $all_products_merged[$j]['product_id']) {

                $all_products_merged[$j]['quantity'] = $all_products_merged[$i]['quantity'] + $all_products_merged[$j]['quantity'];
                $all_products_merged[$j]['price'] = $all_products_merged[$i]['price'] + $all_products_merged[$j]['price'];

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

function getProductsBetweenInterval($startdate, $finishdate, PDO $db) {

    $startdate = (int) $startdate;
    $finishdate = (int) $finishdate;
    $allproducts = array();

    $all_products = $db->prepare(
        'SELECT s01_Orders.orderdate,
                s01_OrderItems.line_id,
                s01_OrderItems.product_id,
                s01_OrderItems.code,
                s01_OrderItems.price,
                s01_OrderItems.name,
                s01_OrderItems.quantity
        FROM s01_Orders
        INNER JOIN s01_OrderItems
        ON s01_OrderItems.order_id=s01_Orders.id
        WHERE s01_Orders.orderdate >= :startdate AND s01_Orders.orderdate <= :finishdate'
    );

    $all_products->execute(array(':startdate' => $startdate, ':finishdate' => $finishdate));

    $all_products = $all_products->fetchAll(PDO::FETCH_ASSOC);

    //ALL VARIANT PRODUCTS IN THIS TIME INTERVAL
    $all_variant_products = getVariantProducts($startdate, $finishdate, $db);

    //ALL NON-VARIANT PRODUCTS IN THIS TIME INTERVAL
    $non_variants = getNonVariantProducts($all_products, $all_variant_products);

    //ALL PRODUCTS MERGED AFTER INDIVIDUAL MERGING PROCESS HAS BEEN COMPLETED
    $merged_variants = mergeVariantParts($all_variant_products);

    //CONTAINS ALL PRODUCTS FROM TIME RANGE
    if ($merged_variants === null) {

        return $non_variants;

    } else {

        $all_products_merged = array_merge($non_variants, $merged_variants);

        //GET VARIANT CODES
        $all_products_merged = getVariantCodes($all_products_merged, $db);
        $all_products_merged = filterNonVariants($all_products_merged);
        $all_products_merged = mergeVariants($all_products_merged);

        return $all_products_merged;

    }

}

function getShippingTotals($startdate, $finishdate, PDO $db) {

    $shipping_totals = $db->prepare(
        'SELECT SUM(s01_OrderCharges.amount)
        FROM s01_Orders
        INNER JOIN s01_OrderCharges
        ON s01_OrderCharges.order_id=s01_Orders.id
        WHERE s01_Orders.orderdate >= :startdate AND s01_Orders.orderdate <= :finishdate'
    );

    $shipping_totals->execute(array(':startdate' => $startdate, ':finishdate' => $finishdate));

    $shipping_total = $shipping_totals->fetch(PDO::FETCH_ASSOC);

    return $shipping_total['SUM(s01_OrderCharges.amount)'];

}

function getCouponTotals($startdate, $finishdate, PDO $db) {

    $coupon_totals = $db->prepare(
        'SELECT SUM(s01_OrderCharges.amount)
        FROM s01_Orders
        INNER JOIN s01_OrderCharges
        ON s01_OrderCharges.order_id=s01_Orders.id
        WHERE s01_OrderCharges.type = "COUPON" AND s01_Orders.orderdate >= :startdate AND s01_Orders.orderdate <= :finishdate'
    );

    $coupon_totals->execute(array(':startdate' => $startdate, ':finishdate' => $finishdate));

    $coupon_total = $coupon_totals->fetch(PDO::FETCH_ASSOC);

    return $coupon_total['SUM(s01_OrderCoupons.total)'];

}

function getCustomFieldValue($productId, $customFieldId, PDO $db) {

    $query = $db->prepare('SELECT value FROM s01_CFM_ProdValues WHERE field_id = :field_id AND product_id = :prod_id');

    $query->execute(array(':field_id' => $customFieldId, ':prod_id' => $productId));

    $value = $query->fetch(PDO::FETCH_ASSOC);

    return $value['value'];

}

function getBasketInventory($productCode, PDO $db) {

    $query = $db->prepare('SELECT SUM(quantity) as count FROM s01_BasketItems WHERE code = :product_code');

    $query->execute(array(':product_code' => $productCode));

    $result = $query->fetch(PDO::FETCH_ASSOC);

    return $result['count'];

}

function getVariantBasketInventory($productCode, PDO $db, $variantId) {

    $query = $db->prepare('SELECT SUM(quantity) as count FROM s01_BasketItems WHERE `code` = :product_code AND `variant_id` = :variant_id');

    $query->execute(array(':product_code' => $productCode, ':variant_id' => $variantId));

    $result = $query->fetch(PDO::FETCH_ASSOC);

    return $result['count'];

}

function getInventory($productId, PDO $db) {

    $query = $db->prepare('SELECT inventory FROM s01_InventoryProductCounts WHERE `product_id` = :product_id');

    $query->execute(array(':product_id' => $productId));

    $result = $query->fetch(PDO::FETCH_ASSOC);

    return $result['inventory'];

}
