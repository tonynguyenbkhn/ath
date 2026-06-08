<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BPG_Generator {

    public function __construct() {
        add_action( 'wp_ajax_bpg_generate_batch', array( $this, 'generate_batch' ) );
    }

    public function generate_batch() {
        check_ajax_referer( 'bpg_generate_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        $batch_size    = isset( $_POST['batch_size'] ) ? intval( $_POST['batch_size'] ) : 5;
        $current_index = isset( $_POST['current_index'] ) ? intval( $_POST['current_index'] ) : 0;
        $total_posts   = isset( $_POST['total_posts'] ) ? intval( $_POST['total_posts'] ) : 1;
        
        $title_tpl     = isset( $_POST['title_template'] ) ? sanitize_text_field( wp_unslash( $_POST['title_template'] ) ) : '';
        $content_tpl   = isset( $_POST['content_template'] ) ? wp_kses_post( wp_unslash( $_POST['content_template'] ) ) : '';
        $post_status   = isset( $_POST['post_status'] ) ? sanitize_text_field( $_POST['post_status'] ) : 'draft';
        $category      = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;
        $image_ids     = isset( $_POST['image_ids'] ) ? explode( ',', sanitize_text_field( $_POST['image_ids'] ) ) : array();
        $image_ids     = array_filter( array_map( 'intval', $image_ids ) );
        $image_option  = isset( $_POST['image_option'] ) ? sanitize_text_field( $_POST['image_option'] ) : 'random';

        $generated = 0;

        for ( $i = 0; $i < $batch_size; $i++ ) {
            $post_num = $current_index + $i + 1;
            
            if ( $post_num > $total_posts ) {
                break;
            }

            // Process Title
            $title = str_replace( '{index}', $post_num, $title_tpl );
            $title = $this->process_spin( $title );

            // Process Content and Images
            $content = $this->process_spin( $content_tpl );
            list( $content, $featured_image_id ) = $this->process_images( $content, $image_ids, $image_option, $post_num );

            $post_data = array(
                'post_title'    => $title,
                'post_content'  => $content,
                'post_status'   => $post_status,
                'post_author'   => get_current_user_id(),
                'post_category' => array( $category ),
            );

            $post_id = wp_insert_post( $post_data );

            if ( ! is_wp_error( $post_id ) ) {
                if ( $featured_image_id ) {
                    set_post_thumbnail( $post_id, $featured_image_id );
                }
                $generated++;
            }
        }

        wp_send_json_success( array(
            'generated_this_batch' => $generated,
            'current_index'        => $current_index + $batch_size,
        ) );
    }

    private function process_spin( $text ) {
        return preg_replace_callback( '/\{spin:([^}]+)\}/', function( $matches ) {
            $options = explode( '|', $matches[1] );
            return $options[ array_rand( $options ) ];
        }, $text );
    }

    private function process_images( $content, $image_ids, $image_option, $post_num ) {
        if ( empty( $image_ids ) ) {
            return array( str_replace( '{image}', '', $content ), 0 );
        }

        $featured_image_id = 0;
        $image_count = 0;

        $content = preg_replace_callback( '/\{image\}/', function( $matches ) use ( &$image_count, $image_ids, $image_option, $post_num, &$featured_image_id ) {
            $idx = 0;
            if ( $image_option === 'random' ) {
                $idx = array_rand( $image_ids );
            } else {
                // Reuse cyclically per post
                $idx = ( ( $post_num - 1 ) * 10 + $image_count ) % count( $image_ids );
            }

            $img_id = $image_ids[ $idx ];
            
            // Set first matching {image} as featured
            if ( $image_count === 0 ) {
                $featured_image_id = $img_id;
            }

            $image_count++;
            return wp_get_attachment_image( $img_id, 'large' );
        }, $content );

        // If no {image} was in content, let's still get a featured image if image ids exist
        if ( $featured_image_id === 0 ) {
            $idx = ( $image_option === 'random' ) ? array_rand( $image_ids ) : ( $post_num - 1 ) % count( $image_ids );
            $featured_image_id = $image_ids[ $idx ];
        }

        return array( $content, $featured_image_id );
    }
}
