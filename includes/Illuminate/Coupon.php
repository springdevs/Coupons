<?php

namespace SpringDevs\Coupons\Illuminate;

/**
 * Coupon Class
 */
class Coupon
{
    public function __construct()
    {
        add_filter('woocommerce_coupon_discount_types', [$this, 'custom_coupon_discount_types'], 10, 1);
    }

    public function custom_coupon_discount_types($discount_types)
    {
        $discount_types['sdwac_product_percent'] = __('Product Adjustment [Percentage]', 'sdevs_coupons');
        $discount_types['sdwac_product_fixed'] = __('Product Adjustment [Fixed]', 'sdevs_coupons');
        $discount_types['sdwac_bulk'] = __('Bulk Discount', 'sdevs_coupons');
        return $discount_types;
    }
}
