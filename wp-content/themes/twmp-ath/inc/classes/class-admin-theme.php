<?php

namespace TWMP_THEME\Inc;

use TWMP_THEME\Inc\Traits\Singleton;

class Admin_Theme
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
        add_action('admin_enqueue_scripts', [$this, 'asl_custom_script']);
    }

    public function asl_custom_script($hook)
    {
        if (strpos($hook, 'create-agile-store') !== false || strpos($hook, 'admin_page_edit-agile-store') !== false) {
            wp_enqueue_script('custom-asl-admin-js', get_template_directory_uri() . '/custom/asl-custom.js', ['jquery'], null, true);
        }
    }
}
