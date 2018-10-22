<?php
/**
 * Plugin Name: HivePress
 * Description: Multipurpose listing & directory plugin.
 * Version: 1.0.1
 * Author: HivePress
 * Author URI: https://hivepress.co/
 * Text Domain: hivepress
 * Domain Path: /languages/
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Define the plugin file.
if ( ! defined( 'HP_CORE_FILE' ) ) {
	define( 'HP_CORE_FILE', __FILE__ );
}

// Include the main plugin class.
if ( ! class_exists( 'HivePress' ) ) {
	require_once dirname( __FILE__ ) . '/includes/class-hivepress.php';
}

/**
 * Returns the main plugin instance.
 *
 * @return HivePress
 */
function hivepress() {
	return HivePress::instance();
}

// Initialize plugin.
hivepress();
