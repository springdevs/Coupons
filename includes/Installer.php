<?php

namespace SpringDevs\Coupons;

/**
 * Class Installer
 * @package SpringDevs\Coupons
 */
class Installer
{
    /**
     * Run the installer
     *
     * @return void
     */
    public function run()
    {
        $this->requirements();
        $this->create_tables();
    }

    /**
     * some requirement actions
     */
    public function requirements()
    {
        if (!get_option("sdwac_first_time_purchase_coupon")) {
            add_option("sdwac_first_time_purchase_coupon", 0);
        }

        if (!get_option("sdwac_show_product_discount")) {
            add_option("sdwac_show_product_discount", "yes");
        }

        if (!get_option("sdwac_multi")) {
            add_option("sdwac_multi", "yes");
        }

        if (!get_option("sdwac_url")) {
            add_option("sdwac_url", "coupon");
        }
    }

    /**
     * Create necessary database tables
     *
     * @return void
     */
    public function create_tables()
    {
        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
    }
}
