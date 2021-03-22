<?php


namespace springdevs\WooAdvanceCoupon\Admin;


class Order
{
    public function __construct()
    {
        add_filter('woocommerce_order_item_get_discount', [$this, 'change_order_discount'], 10, 2);
    }

    public function change_order_discount($value, $obj)
    {
        $order_id = $obj->get_order_id();
        $order = wc_get_order($order_id);
        $order_coupons = $order->get_meta('_sdevs_coupon_meta', true);
        if (!is_array($order_coupons)) $order_coupons = [];
        foreach ($order_coupons as $order_coupon) {
            $coupon_obj = new \WC_Coupon($obj->get_code());
            if ($coupon_obj->get_id() == $order_coupon['coupon']) {
                $value = $order_coupon['discount'];
            }
        }
        return $value;
    }
}
