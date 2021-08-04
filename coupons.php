<?php
/*
Plugin Name: Advance Coupons for Woocommerce
Plugin URI: https://wordpress.org/plugins/advance-coupons-for-woocommerce/
Description: Create gift vouchers, store credits, special discounts based on the amount spent, etc.
Version: 1.0.2
Author: SpringDevs
Author URI: https://springdevs.com/
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: sdevs_coupons
Domain Path: /languages
*/

/**
 * Copyright (c) 2021 SpringDevs (email: contact@springdevs.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Sdevs_coupon class
 *
 * @class Sdevs_coupon The class that holds the entire Sdevs_coupon plugin
 */
final class Sdevs_coupon
{
    /**
     * Plugin version
     *
     * @var string
     */
    const version = '1.0.1';

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = [];

    /**
     * Constructor for the Sdevs_coupon class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    private function __construct()
    {
        $this->define_constants();

        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        add_action('plugins_loaded', [$this, 'init_plugin']);
    }

    /**
     * Initializes the Sdevs_coupon() class
     *
     * Checks for an existing Sdevs_coupon() instance
     * and if it doesn't find one, creates it.
     *
     */
    public static function init()
    {
        static $instance = false;

        if (!$instance) {
            $instance = new Sdevs_coupon();
        }

        return $instance;
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get($prop)
    {
        if (array_key_exists($prop, $this->container)) {
            return $this->container[$prop];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset($prop)
    {
        return isset($this->{$prop}) || isset($this->container[$prop]);
    }

    /**
     * Define the constants
     *
     * @return void
     */
    public function define_constants()
    {
        define('SDEVS_COUPON_VERSION', self::version);
        define('SDEVS_COUPON_FILE', __FILE__);
        define('SDEVS_COUPON_PATH', dirname(SDEVS_COUPON_FILE));
        define('SDEVS_COUPON_INCLUDES', SDEVS_COUPON_PATH . '/includes');
        define('SDEVS_COUPON_URL', plugins_url('', SDEVS_COUPON_FILE));
        define('SDEVS_COUPON_ASSETS', SDEVS_COUPON_URL . '/assets');
    }

    /**
     * Load the plugin after all plugis are loaded
     *
     * @return void
     */
    public function init_plugin()
    {
        if (class_exists('WooCommerce')) {
            $this->includes();
            $this->init_hooks();
        } else {
            add_action('admin_notices', function () {
                include 'includes/Admin/views/plugin-notice.php';
            });
        }
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate()
    {
        $installer = new SpringDevs\Coupons\Installer();
        $installer->run();
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate()
    {
    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes()
    {
        if ($this->is_request('admin')) {
            $this->container['admin'] = new SpringDevs\Coupons\Admin();
        }

        if ($this->is_request('frontend')) {
            $this->container['frontend'] = new SpringDevs\Coupons\Frontend();
        }

        if ($this->is_request('ajax')) {
            $this->container['ajax'] = new SpringDevs\Coupons\Ajax();
        }
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks()
    {
        add_action('init', [$this, 'init_classes']);

        // Localize our plugin
        add_action('init', [$this, 'localization_setup']);
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes()
    {
        if ($this->is_request('ajax')) {
            // $this->container['ajax'] =  new sdwac_coupon\sdwac_coupon_Coupon\Ajax();
        }
        $this->container['api']    = new SpringDevs\Coupons\Api();
        $this->container['assets'] = new SpringDevs\Coupons\Assets();
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup()
    {
        load_plugin_textdomain('sdevs_coupons', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin();

            case 'ajax':
                return defined('DOING_AJAX');

            case 'rest':
                return defined('REST_REQUEST');

            case 'cron':
                return defined('DOING_CRON');

            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }
} // Sdevs_coupon

/**
 * Initialize the main plugin
 *
 * @return \sdevs_coupon|bool
 */
function sdevs_coupon()
{
    return Sdevs_coupon::init();
}

/**
 *  kick-off the plugin
 */
sdevs_coupon();
