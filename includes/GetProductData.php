<?php
/**
* GetProductData
*/

class GetProductData
{
    
    public function getNonVariantProducts($allProducts, $allVariants) {

        $captured = array();

        foreach ($allProducts as $key => $product) {
            
            foreach ($allVariants as $variant) {
                
                if ($product['code'] == $variant['code']) {

                    $captured[] = $key;

                }

            }

        }

        foreach ($captured as $value) {

            unset($allProducts[$value]);

        }

        $allProducts = array_values($allProducts);
        $allProducts = $this->addBlankVariantCodes($allProducts);

        return $allProducts;

    }

    public function getVariantProductsBetweenInterval($startdate, $finishdate, PDO $db) {

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
                    s01_OrderOptions.option_id,
                    s01_OrderOptions.price as attr_price
            FROM s01_Orders
            INNER JOIN s01_OrderItems
            ON s01_OrderItems.order_id=s01_Orders.id
            INNER JOIN s01_OrderOptions
            ON s01_OrderOptions.line_id=s01_OrderItems.line_id
            WHERE s01_Orders.orderdate >= :startdate AND s01_Orders.orderdate <= :finishdate'
        );

        $variant_products->execute(array(':startdate' => $startdate, ':finishdate' => $finishdate));

        $result = $variant_products->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function getProductsBetweenInterval($startdate, $finishdate, PDO $db) {
    
        $startdate = (int) $startdate;
        $finishdate = (int) $finishdate;

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

        return $all_products->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getVariantCodes($variants, PDO $db) {

        //Go through each product and assign variant_code
        foreach ($variants as &$product) {

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
                            s01_ProductVariants.variant_id,
                            s01_Products.name
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
                $product['variant_name'] = $result['name'];

            }

        }

        return $variants;

    }

    public function filterNonVariants(&$products) {

        foreach ($products as $key => $product) {

            if ($product['variant_code'] == '') {

                unset($products[$key]);

            } 

        }

        return array_values($products);

    }

    public function addBlankVariantCodes($products) {

        foreach ($products as &$product) {
            
            $product['attr_id'] = '';
            $product['attr_code'] = '';
            $product['option_id'] = '';
            $product['variant_code'] = '';
            $product['variant_id'] = '';
            $product['variant_name'] = '';

        }

        return $products;

    }

    public function getProductCustomFieldValue($productId, $customFieldId, PDO $db) {

        $query = $db->prepare('SELECT value FROM s01_CFM_ProdValues WHERE field_id = :field_id AND product_id = :prod_id');

        $query->execute(array(':field_id' => $customFieldId, ':prod_id' => $productId));

        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result['value'];

    }

    public function getBasketInventory($productCode, PDO $db) {

        $query = $db->prepare('SELECT SUM(quantity) as count FROM s01_BasketItems WHERE code = :product_code');

        $query->execute(array(':product_code' => $productCode));

        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result['count'];

    }

    public function getInventory($productId, PDO $db) {

        $query = $db->prepare('SELECT inventory FROM s01_InventoryProductCounts WHERE `product_id` = :product_id');

        $query->execute(array(':product_id' => $productId));

        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result['inventory'];

    }

    public function getVariantBasketInventory($productCode, PDO $db, $variantId) {

        $query = $db->prepare('SELECT SUM(quantity) as count FROM s01_BasketItems WHERE `code` = :product_code AND `variant_id` = :variant_id');

        $query->execute(array(':product_code' => $productCode, ':variant_id' => $variantId));

        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result['count'];

    }

}
