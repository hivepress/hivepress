<?php
/**
 * Plugin Name: HivePress
 * Description: Multipurpose directory, listing & classifieds plugin.
 * Version: 1.2.1
 * Author: HivePress
 * Author URI: https://hivepress.io/
 * Text Domain: hivepress
 * Domain Path: /languages/
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Define the core HivePress file.
if ( ! defined( 'HP_CORE_FILE' ) ) {
	define( 'HP_CORE_FILE', __FILE__ );
}

// Include the core HivePress class.
require_once __DIR__ . '/includes/class-core.php';

/**
 * Returns the core HivePress instance.
 *
 * @return HivePress\Core
 */
function hivepress() {
	return HivePress\Core::instance();
}

// Initialize HivePress.
hivepress();
