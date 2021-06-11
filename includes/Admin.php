<?php

namespace SpringDevs\Coupons;

use SpringDevs\Coupons\Admin\Coupon;
use SpringDevs\Coupons\Admin\MetaBoxes;
use SpringDevs\Coupons\Admin\Order;
use SpringDevs\Coupons\Admin\sdwac_Panels;
use SpringDevs\Coupons\Admin\Setting;
use SpringDevs\Coupons\Illuminate\Coupon as IlluminateCoupon;

/**
 * The admin class
 */
class Admin
{

    /**
     * Initialize the class
     */
    public function __construct()
    {
        $this->dispatch_actions();
        new IlluminateCoupon();
        new Coupon();
        new MetaBoxes();
        new sdwac_Panels();
        new Setting();
        new Order();
    }

    /**
     * Dispatch and bind actions
     *
     * @return void
     */
    public function dispatch_actions()
    {
    }
}
