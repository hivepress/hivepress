<?php
/**
 * Plugin Name: HivePress
 * Plugin URI: https://hivepress.io/
 * Description: Multipurpose directory, listing & classifieds plugin.
 * Version: 1.7.10
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
 * Returns HivePress core instance.
 *
 * @return HivePress\Core
 */
function hivepress() {
	return HivePress\Core::instance();
}

// Initialize HivePress.
hivepress();
