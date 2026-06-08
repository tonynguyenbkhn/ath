<?php

namespace TWMP_THEME\Inc;

use TWMP_THEME\Inc\Traits\Singleton;

class Blocks_Theme
{
    use Singleton;

    protected function __construct()
    {
        $this->setup_hooks();
    }

    protected function setup_hooks()
    {
        add_action('after_setup_theme', [$this, 'disable_duotone_support'], 20);
        add_action('init', [$this, 'register_block_styles']);
        add_action('init', [$this, 'register_block_patterns']);
    }

    public function disable_duotone_support()
    {
        remove_theme_support('wp-duotone');

        if (!class_exists('\WP_Duotone')) {
            return;
        }

        remove_filter('render_block', ['WP_Duotone', 'render_duotone_support'], 10);
        remove_filter('render_block_core/image', ['WP_Duotone', 'restore_image_outer_container'], 10);
        remove_action('wp_enqueue_scripts', ['WP_Duotone', 'output_block_styles'], 9);
        remove_action('wp_enqueue_scripts', ['WP_Duotone', 'output_global_styles'], 11);
        remove_action('wp_footer', ['WP_Duotone', 'output_footer_assets'], 10);
    }

    public function register_block_styles()
    {
        register_block_style(
            'core/paragraph',
            [
                'name'  => 'highlight',
                'label' => __('Highlight', 'twmp-ath'),
            ]
        );
    }

    public function register_block_patterns()
    {
        register_block_pattern(
            'twmp-ath/hero-simple',
            [
                'title'   => __('Hero Simple', 'twmp-ath'),
                'content' => '<!-- wp:heading --><h2>Title</h2><!-- /wp:heading -->',
            ]
        );
    }
}
