<?php
/**
 * Plugin Name: HivePress
 * Description: Multipurpose listing & directory plugin.
 * Version: 1.1.0
 * Author: HivePress
 * Author URI: https://hivepress.io/
 * Text Domain: hivepress
 * Domain Path: /languages/
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Require autoloading functions.
require_once __DIR__ . '/includes/autoload.php';

/**
 * Returns the main plugin instance.
 *
 * @return HivePress\Core
 */
function hivepress() {
	return HivePress\Core::instance();
}

// Initialize plugin.
hivepress();
