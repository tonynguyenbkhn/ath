<?php

namespace TWMP_THEME\Inc;

use TWMP_THEME\Inc\Traits\Singleton;

class Sidebars_Theme
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

        /**
         * Actions
         */
        add_action('widgets_init', [$this, 'register_sidebars']);
    }

    public function register_sidebars()
    {

        register_sidebar(
            [
                'name'          => esc_html__('Footer Top', 'twmp-phonghoa'),
                'id'            => 'footer-top',
                'description'   => '',
                'before_widget' => '<div id="%1$s" class="widget widget-sidebar %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title__footer-top">',
                'after_title'   => '</h3>',
            ]
        );

        register_sidebar(
            [
                'name'          => esc_html__('Footer Primary', 'twmp-phonghoa'),
                'id'            => 'footer-primary',
                'description'   => '',
                'before_widget' => '<div id="%1$s" class="widget widget-sidebar %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title__footer-primary">',
                'after_title'   => '</h3>',
            ]
        );

        register_sidebar(
            [
                'name'          => esc_html__('Footer Absolute', 'twmp-phonghoa'),
                'id'            => 'footer-absolute',
                'description'   => '',
                'before_widget' => '<div id="%1$s" class="widget widget-sidebar %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title__footer-absolute">',
                'after_title'   => '</h3>',
            ]
        );

        register_sidebar(
            [
                'name'          => esc_html__('Header Top', 'twmp-phonghoa'),
                'id'            => 'header-top',
                'description'   => '',
                'before_widget' => '<div id="%1$s" class="widget widget__header-top widget-sidebar %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title__header-top">',
                'after_title'   => '</h3>',
            ]
        );

        register_sidebar(
            [
                'name'          => esc_html__('Header Top Home', 'twmp-phonghoa'),
                'id'            => 'header-top-home',
                'description'   => '',
                'before_widget' => '<div id="%1$s" class="widget widget__header-top widget-sidebar %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title__header-top">',
                'after_title'   => '</h3>',
            ]
        );

        register_sidebar(
            [
                'name'          => esc_html__('Sidebar Category', 'twmp-phonghoa'),
                'id'            => 'sidebar-category',
                'description'   => '',
                'before_widget' => '<div id="%1$s" class="widget widget-sidebar %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="widget-title">',
                'after_title'   => '</h3>',
            ]
        );

        register_sidebar(array(
            'name'          => esc_html__('Sidebar Archive Woocommerce', 'twmp-phonghoa'),
            'id'            => 'sidebar-archive-woocommerce',
            'before_widget' => '<aside id="%1$s" class="widget widget--sidebar-archive-woocommerce %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h2 class="widget__title">',
            'after_title'   => '</h2>',
        ));
    }
}
