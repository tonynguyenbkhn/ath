<?php

namespace TWMP_THEME\Inc;

use TWMP_THEME\Inc\Traits\Singleton;

class Media_Theme
{
    use Singleton;

    protected function __construct()
    {
        $this->setup_hooks();
    }

    protected function setup_hooks()
    {
        add_filter('intermediate_image_sizes_advanced', [$this, 'remove_default_image_sizes']);
        add_filter('wp_img_tag_add_auto_sizes', '__return_false');
    }

    public function remove_default_image_sizes($sizes)
    {
        unset($sizes['1536x1536']);
        unset($sizes['2048x2048']);

        return $sizes;
    }
}
