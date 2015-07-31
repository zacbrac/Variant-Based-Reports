<?php
/**
 * MergeProductData
 */

class MergeProductData {

    public function mergeNonVariantProducts($nonVariantProducts) {

        $mergedNonVariants = array();

        foreach ($nonVariantProducts as $key => $product) {

            // var_dump($product);

            $mergedNonVariants[$product['code']]['line_id'] = $product['line_id'];
            $mergedNonVariants[$product['code']]['product_id'] = $product['product_id'];
            $mergedNonVariants[$product['code']]['code'] = $product['code'];
            $mergedNonVariants[$product['code']]['name'] = $product['name'];

            if (array_key_exists('attr_id', $mergedNonVariants[$product['code']])) {

                $mergedNonVariants[$product['code']]['attr_id'] .= ',' . $product['attr_id'];
                $mergedNonVariants[$product['code']]['attr_code'] .= ',' . $product['attr_code'];
                $mergedNonVariants[$product['code']]['option_id'] .= ',' . $product['option_id'];
                $mergedNonVariants[$product['code']]['quantity'] += $product['quantity'];
                $mergedNonVariants[$product['code']]['total_revenue'] += $product['price'] * $product['quantity'];

            } else {
                
                $mergedNonVariants[$product['code']]['attr_id'] = $product['attr_id'];
                $mergedNonVariants[$product['code']]['attr_code'] = $product['attr_code'];
                $mergedNonVariants[$product['code']]['option_id'] = $product['option_id'];
                $mergedNonVariants[$product['code']]['quantity'] = $product['quantity'];
                $mergedNonVariants[$product['code']]['total_revenue'] = $product['price'] * $product['quantity'];

            }

        }

        return array_values($mergedNonVariants);

    }

    public function mergeVariants($variantProducts) {

        $mergedVariants = array();

        foreach ($variantProducts as $product) {
            
            if (isset($product['code']) && $product['code'] != '') {

                $mergedVariants[$product['code']]['line_id'] = $product['line_id'];
                $mergedVariants[$product['code']]['product_id'] = $product['product_id'];
                $mergedVariants[$product['code']]['code'] = $product['code'];
                $mergedVariants[$product['code']]['name'] = $product['name'];
                $mergedVariants[$product['code']]['variant_name'] = $product['variant_name'];
                $mergedVariants[$product['code']]['variant_code'] = $product['variant_code'];

                if (array_key_exists('attr_id', $mergedVariants[$product['code']])) {

                    $mergedVariants[$product['code']]['attr_id'] .= ',' . $product['attr_id'];
                    $mergedVariants[$product['code']]['attr_code'] .= ',' . $product['attr_code'];
                    $mergedVariants[$product['code']]['option_id'] .= ',' . $product['option_id'];
                    $mergedVariants[$product['code']]['quantity'] += $product['quantity'];
                    $mergedVariants[$product['code']]['total_revenue'] += $product['price'] * $product['quantity'];

                } else {
                    
                    $mergedVariants[$product['code']]['attr_id'] = $product['attr_id'];
                    $mergedVariants[$product['code']]['attr_code'] = $product['attr_code'];
                    $mergedVariants[$product['code']]['option_id'] = $product['option_id'];
                    $mergedVariants[$product['code']]['quantity'] = $product['quantity'];
                    $mergedVariants[$product['code']]['total_revenue'] = $product['price'] * $product['quantity'];

                }

            }

        }

        return array_values($mergedVariants);

    }

    public function mergeVariantParts($products) {

        $mergedVariants = array();

        foreach ($products as $product) {
            
            $mergedVariants[$product['line_id']]['line_id'] = $product['line_id'];
            $mergedVariants[$product['line_id']]['product_id'] = $product['product_id'];
            $mergedVariants[$product['line_id']]['code'] = $product['code'];
            $mergedVariants[$product['line_id']]['name'] = $product['name'];
            $mergedVariants[$product['line_id']]['price'] = $product['price'];

            if (array_key_exists('attr_id', $mergedVariants[$product['line_id']])) {

                $mergedVariants[$product['line_id']]['attr_id'] .= ',' . $product['attr_id'];
                $mergedVariants[$product['line_id']]['attr_code'] .= ',' . $product['attr_code'];
                $mergedVariants[$product['line_id']]['option_id'] .= ',' . $product['option_id'];
                $mergedVariants[$product['line_id']]['attr_price'] += $product['attr_price'];

            } else {
                
                $mergedVariants[$product['line_id']]['attr_id'] = $product['attr_id'];
                $mergedVariants[$product['line_id']]['attr_code'] = $product['attr_code'];
                $mergedVariants[$product['line_id']]['option_id'] = $product['option_id'];
                $mergedVariants[$product['line_id']]['quantity'] = $product['quantity'];
                $mergedVariants[$product['line_id']]['attr_price'] = $product['attr_price'];

            }

        }

        return array_values($mergedVariants);

    }

}
