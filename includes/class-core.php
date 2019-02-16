<?php
/**
 * HivePress core.
 *
 * @package HivePress
 */

namespace HivePress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * HivePress core class.
 *
 * @class Core
 */
final class Core {

	/**
	 * The single instance of the class.
	 *
	 * @var Core
	 */
	private static $instance;

	/**
	 * Array of component instances.
	 *
	 * @var array
	 */
	private $components = [];

	// Forbid cloning and duplicating instances.
	private function __clone() {}
	private function __wakeup() {}

	/**
	 * Class constructor.
	 */
	private function __construct() {
		// todo.
		add_action( 'plugins_loaded', [ $this, 'setup' ] );
	}

	// todo.
	public function setup() {
		$dirs = [ 'C:\xampp\htdocs\hivepress\wp-content\plugins\hivepress' ];

		foreach ( $dirs as $dir ) {
			foreach ( glob( $dir . '/includes/components/*.php' ) as $filepath ) {

			}
		}
	}

	/**
	 * Ensures only one plugin instance is loaded.
	 *
	 * @see hivepress()
	 * @return Core
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
