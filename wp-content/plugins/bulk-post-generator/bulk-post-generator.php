<?php
/**
 * Plugin Name: Bulk Post Generator
 * Description: Automatically generate multiple posts with randomized or reused content and images.
 * Version: 1.0.0
 * Author: AI
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BPG_VERSION', '1.0.0' );
define( 'BPG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BPG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include core classes
require_once BPG_PLUGIN_DIR . 'includes/class-bpg-admin.php';
require_once BPG_PLUGIN_DIR . 'includes/class-bpg-generator.php';

// Initialize
function bpg_init() {
    new BPG_Admin();
    new BPG_Generator();
}
add_action( 'plugins_loaded', 'bpg_init' );
