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

// Include autoloading functions.
require_once __DIR__ . '/includes/autoload.php';

/**
 * Returns the main HivePress instance.
 *
 * @return HivePress\Core
 */
function hivepress() {
	return HivePress\Core::instance();
}

// Initialize HivePress.
hivepress();

// todo remove.
add_action('init', function() {
	$form = new HivePress\Forms\User_Login();
	echo $form->render();
	die();
});
