<?php
/**
 * Plugin Name: HivePress
 * Description: Multipurpose directory, listing & classifieds plugin.
 * Version: 1.6.0
 * Author: HivePress
 * Author URI: https://hivepress.io/
 * Text Domain: hivepress
 * Domain Path: /languages/
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Define the core file.
if ( ! defined( 'HP_FILE' ) ) {
	define( 'HP_FILE', __FILE__ );
}

// Include the core class.
require_once __DIR__ . '/includes/class-core.php';

/**
 * Returns the core instance.
 *
 * @return HivePress\Core
 */
function hivepress() {
	return HivePress\Core::instance();
}

// Initialize HivePress.
hivepress();
