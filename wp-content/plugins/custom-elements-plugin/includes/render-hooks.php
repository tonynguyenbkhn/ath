<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Render Custom Elements
add_action('init', 'custom_elements_render_hooks');

function custom_elements_render_hooks()
{
    $elements = get_posts([
        'post_type'   => 'custom_element',
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    foreach ($elements as $element) {
        $hook = get_post_meta($element->ID, '_custom_elements_hook', true);

        if ($hook) {
            add_action($hook, function () use ($element) {
                function custom_elements_render_hooks()
                {
                    $elements = get_posts(
                        array(
                            'post_type'   => 'custom_element',
                            'post_status' => 'publish',
                            'numberposts' => -1,
                        )
                    );

                    foreach ($elements as $element) {
                        $hook = get_post_meta($element->ID, '_custom_elements_hook', true);

                        if ($hook) {
                            add_action(
                                $hook,
                                function () use ($element) {
                                    echo wp_kses_post(apply_filters('the_content', $element->post_content));
                                }
                            );
                        }
                    }
                }
            });
        }
    }
}

add_shortcode('custom_element', 'custom_elements_shortcode_callback');

function custom_elements_shortcode_callback($atts)
{
    $atts = shortcode_atts([
        'id' => 0,
    ], $atts);

    $post = get_post($atts['id']);
    if ($post && $post->post_type === 'custom_element' && $post->post_status === 'publish') {
        $content = $post->post_content;

        if (has_blocks($content)) {
            $blocks = parse_blocks($content);
            $rendered_content = '';

            foreach ($blocks as $block) {
                // Render block và xử lý shortcode trong nội dung block
                $rendered_block = render_block($block);
                $rendered_content .= do_shortcode($rendered_block);
            }

            return $rendered_content;
        }

        // Nếu không có block, chỉ xử lý shortcode
        return do_shortcode($content);
    }

    return '';
}
