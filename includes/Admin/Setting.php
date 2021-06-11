<?php

namespace SpringDevs\Coupons\Admin;

/**
 * Setting class
 * Woocommerce Settings Tabs
 */
class Setting
{
    public function __construct()
    {
        add_filter('woocommerce_get_sections_wcma', [$this, 'add_section'], 20);
        add_filter('woocommerce_get_settings_wcma', [$this, 'settings_content']);
    }

    public function add_section($sections)
    {
        $sections['coupon'] = __('Coupon', 'sdevs_coupons');
        return $sections;
    }

    public function settings_content($settings)
    {
        global $current_section;
        if ($current_section == 'coupon') {
            $coupon_settings = [];

            $coupon_settings[] = [
                'name' => __('Coupon Settings', 'sdevs_coupons'),
                'type' => 'title',
                'desc' => __('The following options are used to configure Coupon Module', 'sdevs_coupons'),
                'id'   => 'coupon',
            ];

            $args = array(
                'posts_per_page' => -1,
                'order'          => 'asc',
                'post_type'      => 'shop_coupon',
                'post_status'    => 'publish',
            );
            $coupons       = get_posts($args);
            $sdwac_coupons = ["0" => "Select Discount"];
            foreach ($coupons as $data) {
                $sdwac_coupons[$data->ID] = $data->post_title;
            }

            // first time purchase coupon
            $coupon_settings[] = array(
                'name'    => __('Coupon for first Purchase', 'sdevs_coupons'),
                'id'      => 'sdwac_first_time_purchase_coupon',
                'type'    => 'select',
                'options' => $sdwac_coupons,
                'desc'    => __('Select a discount from here which you want to enable for new customers', 'sdevs_coupons'),
            );

            // price cut from
            $coupon_settings[] = array(
                'name'    => __('Price Cut From', 'sdevs_coupons'),
                'id'      => 'sdwac_price_cut_from',
                'type'    => 'select',
                'options' => [
                    'regular' => __('Regular price', 'sdevs_coupons'),
                    'sale'    => __('Sale price', 'sdevs_coupons'),
                ],
            );

            // Multi Coupon
            $coupon_settings[] = array(
                'name'    => __('Multi Coupon', 'sdevs_coupons'),
                'id'      => 'sdwac_multi',
                'type'    => 'select',
                'options' => [
                    'yes' => __('Yes', 'sdevs_coupons'),
                    'no'  => __('No', 'sdevs_coupons'),
                ],
            );

            // Coupon Url slug Name
            $coupon_settings[] = array(
                'name' => __('Coupon Url slug Name', 'sdevs_coupons'),
                'id'   => 'sdwac_url',
                'type' => 'text',
                'desc' => get_home_url() . '/?<b>' . get_option('sdwac_url') . '</b>=coupon_code',
            );

            $coupon_settings[] = array('type' => 'sectionend', 'id' => 'coupon');
            return $coupon_settings;
        }
        return $settings;
    }
}
