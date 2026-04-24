<?php

namespace TWMP_THEME\Inc;

use TWMP_THEME\Inc\Traits\Singleton;

class Woo_Theme
{

    use Singleton;

    /**
     * Construct method.
     */
    protected function __construct()
    {
        $this->setup_hooks();
    }

    /**
     * To register action/filter.
     *
     * @return void
     */
    protected function setup_hooks()
    {
        require_once get_theme_file_path('/inc/woocommerces/cities/tinh_thanhpho.php');
        require_once get_theme_file_path('/inc/woocommerces/cities/quan_huyen.php');
        require_once get_theme_file_path('/inc/woocommerces/cities/xa_phuong_thitran.php');
        require_once get_theme_file_path('/inc/woocommerces/global.php');
        require_once get_theme_file_path('/inc/woocommerces/helper.php');
        require_once get_theme_file_path('/inc/woocommerces/api.php');
        require_once get_theme_file_path('/inc/woocommerces/single.php');
        require_once get_theme_file_path('/inc/woocommerces/archive.php');
        // require_once get_theme_file_path('/inc/woocommerces/cart.php');
        require_once get_theme_file_path('/inc/woocommerces/checkout.php');
        require_once get_theme_file_path('/inc/woocommerces/thank-you.php');
        require_once get_theme_file_path('/inc/woocommerces/account.php');
        require_once get_theme_file_path('/inc/woocommerces/order-tracking.php');
        require_once get_theme_file_path('/inc/woocommerces/warranty-tracking.php');
    }
}
