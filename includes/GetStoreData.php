<?php
/**
* GetStoreData
*/

class GetStoreData
{
    
    public function getShippingTotals($startdate, $finishdate, \PDO $db) {

        $shipping_totals = $db->prepare(
            'SELECT SUM(s01_OrderCharges.amount)
            FROM s01_Orders
            INNER JOIN s01_OrderCharges
            ON s01_OrderCharges.order_id=s01_Orders.id
            WHERE s01_Orders.orderdate >= :startdate AND s01_Orders.orderdate <= :finishdate'
        );

        $shipping_totals->execute(array(':startdate' => $startdate, ':finishdate' => $finishdate));

        $result = $shipping_totals->fetch(\PDO::FETCH_ASSOC);

        return $result['SUM(s01_OrderCharges.amount)'];

    }

    public function getCouponTotals($startdate, $finishdate, \PDO $db) {

        $coupon_totals = $db->prepare(
            'SELECT SUM(s01_OrderCharges.amount)
            FROM s01_Orders
            INNER JOIN s01_OrderCharges
            ON s01_OrderCharges.order_id=s01_Orders.id
            WHERE s01_OrderCharges.type = "COUPON" AND s01_Orders.orderdate >= :startdate AND s01_Orders.orderdate <= :finishdate'
        );

        $coupon_totals->execute(array(':startdate' => $startdate, ':finishdate' => $finishdate));

        $result = $coupon_totals->fetch(\PDO::FETCH_ASSOC);

        return $result['SUM(s01_OrderCharges.amount)'];

    }

}
