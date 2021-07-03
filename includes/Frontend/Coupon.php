<?php

namespace SpringDevs\Coupons\Frontend;

/**
 *
 * Class Coupon
 *
 * apply coupon feature
 *
 */
class Coupon
{
    public function __construct()
    {
        // add_filter('woocommerce_get_price_html', [$this, 'update_price_html'], 10, 2);
        add_filter('woocommerce_product_get_price', [$this, 'update_product_price'], 10, 2);
        add_filter('woocommerce_cart_item_price', [$this, 'change_cart_table_price_display'], 30, 3);
        add_filter('woocommerce_coupon_custom_discounts_array', [$this, 'custom_discount_for_bulk_coupon'], 10, 2);
        add_filter('woocommerce_cart_totals_coupon_html', [$this, 'change_product_coupon_html'], 30, 3);
        add_filter('woocommerce_product_variation_get_price', [$this, 'update_product_price'], 10, 2);
        add_filter('woocommerce_variation_prices_price', [$this, 'update_product_price'], 99, 2);
    }

    /**
     * @param $price_html
     * @param $product
     * @return mixed|string
     */
    public function update_price_html($price_html, $product)
    {
        if ($product->get_regular_price() != $product->get_price() && !$product->is_type('variable'))
            $price_html = wc_format_sale_price($product->get_regular_price(), $product->get_price());
        return $price_html;
    }

    public function update_product_price($price, $product)
    {
        if (is_admin()) return $price;
        $coupons = WC()->cart->applied_coupons;
        $discount = 0;
        $avaiable_product_ids = [];
        foreach ($coupons as $coupon) {
            $couponData = new \WC_Coupon($coupon);
            $coupon_id = $couponData->get_id();
            $post_meta = get_post_meta($coupon_id, '_sdwac_coupon_meta', true);
            if (!(empty($post_meta) || !is_array($post_meta) || !isset($post_meta['type'])))
                if ($post_meta['type'] == 'sdwac_product_fixed' || $post_meta['type'] == 'sdwac_product_percent') {
                    $product_ids = $couponData->get_product_ids();
                    foreach ($product_ids as $product_id) array_push($avaiable_product_ids, $product_id);
                }
            if ($post_meta['type'] == 'sdwac_product_fixed') {
                $discount += $couponData->get_amount();
            } elseif ($post_meta['type'] == 'sdwac_product_percent') {
                $discount += ($couponData->get_amount() / 100) * $price;
            }
        }
        if ($discount == 0) {
            return $price;
        } else {
            if (get_option('sdwac_price_cut_from', 'regular') == 'regular') $price = (float)$product->get_regular_price();
            return $this->change_product_price($price, $discount, $product->get_id(), $avaiable_product_ids);
        }
    }

    public function change_product_price($price, $discount, $product_id, $product_ids)
    {
        if ($price < $discount) return $price = 0;
        if (!is_array($product_ids) || count($product_ids) == 0) {
            return $price - $discount;
        } else {
            if (in_array($product_id, $product_ids)) return $price - $discount;
        }
    }

    public function change_cart_table_price_display($price, $values, $cart_item_key)
    {
        $slashed_price = $values['data']->get_price_html();
        if ($values['variation_id'] == 0) {
            $product = wc_get_product($values['product_id']);
        } else {
            $product = wc_get_product($values['variation_id']);
        }
        if ($product->get_regular_price() > $values['data']->get_price()) $price = $slashed_price;
        return $price;
    }

    public function custom_discount_for_bulk_coupon($discounts, $coupon)
    {
        if ($coupon->get_discount_type() == 'sdwac_bulk') {
            foreach ($discounts as $key => $value) {
                $discounts[$key] = $this->get_bulk_discount($coupon);
                break;
            }
        }
        return $discounts;
    }

    /**
     * Bulk Discount
     *
     * @param object $coupon
     * @return discount
     */
    public function get_bulk_discount($coupon)
    {
        $coupon_id = $coupon->get_id();
        $post_meta = get_post_meta($coupon_id, '_sdwac_coupon_meta', true);
        $cart = WC()->cart;
        $bulk_discount = 0;
        foreach ($post_meta['discounts'] as $discount) {
            if ($discount['min'] <= $cart->subtotal && $discount['max'] >= $cart->subtotal) {
                switch ($discount["type"]) {
                    case 'percentage':
                        $bulk_discount = (($discount["value"] / 100) * $cart->subtotal) * 100;
                        break;
                    case 'fixed':
                        $bulk_discount = $discount["value"] * 100;
                        break;
                }
            }
        }
        return $bulk_discount;
    }

    /**
     * change product coupon html
     *
     * @param $coupon_html
     * @param $coupon
     * @param $discount_amount_html
     * @return coupon_html
     */
    public function change_product_coupon_html($coupon_html, $coupon, $discount_amount_html)
    {
        $coupon_meta = get_post_meta($coupon->get_id(), "_sdwac_coupon_meta", true);
        if (empty($coupon_meta) || !is_array($coupon_meta) || !isset($coupon_meta['type'])) return $coupon_html;

        if ($coupon_meta["type"] == "sdwac_product_fixed" || $coupon_meta['type'] == 'sdwac_product_percent') {
            if ($coupon_meta["type"] == "sdwac_product_percent") {
                $discount_amount_html = '[on products] <span class="woocommerce-Price-amount amount">' . $coupon->get_amount() . '%</span>';
            } else {
                $discount_amount_html = '[on products] ' . wc_price($coupon->get_amount());
            }
            $coupon_html          = $discount_amount_html . ' <a class="woocommerce-remove-coupon" href="' . esc_url(add_query_arg('remove_coupon', urlencode($coupon->get_code()), defined('WOOCOMMERCE_CHECKOUT') ? wc_get_checkout_url() : wc_get_cart_url())) . '" class="woocommerce-remove-coupon" data-coupon="' . esc_attr($coupon->get_code()) . '">' . __('[Remove]', 'sdevs_coupons') . '</a>';
        }
        return $coupon_html;
    }
}
