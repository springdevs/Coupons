<?php


namespace springdevs\WooAdvanceCoupon\Frontend;


class Checkout
{
    public function __construct()
    {
        add_action('woocommerce_checkout_update_order_meta', [$this, 'save_checkout_data']);
    }

    /**
     * Save coupon data as order meta
     *
     * @param $order_id
     */
    public function save_checkout_data($order_id)
    {
        $coupons = WC()->cart->applied_coupons;
        if (!is_array($coupons)) $coupons = [];
        $data = [];
        foreach ($coupons as $coupon) {
            $coupon = new \WC_Coupon($coupon);
            if ($coupon->get_discount_type() === 'sdwac_bulk') {
                $coupon_cls = new Coupon();
                $discount = $coupon_cls->get_bulk_discount($coupon);
                array_push($data, [
                    'coupon' => $coupon->get_id(),
                    'coupon_type' => $coupon->get_discount_type(),
                    'discount' => $discount
                ]);
            } elseif ($coupon->get_discount_type() === 'sdwac_product_percent') {
                $product_ids = $coupon->get_product_ids();
                $discount = 0;
                foreach (WC()->cart->get_cart() as $value) {
                    if (in_array($value['product_id'], $product_ids)) {
                        $product = wc_get_product($value['product_id']);
                        $price = $product->get_price();
                        if (get_option('sdwac_price_cut_from', 'regular') == 'regular') {
                            $price = (float)$product->get_regular_price();
                        }
                        $discount += (($coupon->get_amount() / 100) * $price) * $value['quantity'];
                    }
                }
                array_push($data, [
                    'coupon' => $coupon->get_id(),
                    'coupon_type' => $coupon->get_discount_type(),
                    'discount' => $discount
                ]);
            } elseif ($coupon->get_discount_type() === 'sdwac_product_fixed') {
                $product_ids = $coupon->get_product_ids();
                $discount = 0;
                foreach (WC()->cart->get_cart() as $value) {
                    if (in_array($value['product_id'], $product_ids)) {
                        $discount += ($coupon->get_amount() * $value['quantity']);
                    }
                }
                array_push($data, [
                    'coupon' => $coupon->get_id(),
                    'coupon_type' => $coupon->get_discount_type(),
                    'discount' => $discount
                ]);
            } else {
                array_push($data, [
                    'coupon' => $coupon->get_id(),
                    'coupon_type' => $coupon->get_discount_type(),
                    'discount' => $coupon->get_amount()
                ]);
            }
        }
        update_post_meta($order_id, '_sdevs_coupon_meta', $data);
    }
}
