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
	 * Array of HivePress configuration.
	 *
	 * @var array
	 */
	private $config = [];

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
		require_once 'C:\xampp\htdocs\hivepress\wp-content\plugins\hivepress\includes\helpers.php';
		$dirs = [ 'C:\xampp\htdocs\hivepress\wp-content\plugins\hivepress' ];

		foreach ( $dirs as $dir ) {
			foreach ( glob( $dir . '/includes/components/*.php' ) as $filepath ) {

			}
		}

		$this->config=include 'C:\xampp\htdocs\hivepress\wp-content\plugins\hivepress\includes\config.php';

		define( 'HP_CORE_NAME', 'HivePress' );
		define( 'HP_CORE_PATH', plugin_dir_path( 'C:\xampp\htdocs\hivepress\wp-content\plugins\hivepress\hivepress.php' ) );
		define( 'HP_CORE_URL', plugin_dir_url( 'C:\xampp\htdocs\hivepress\wp-content\plugins\hivepress\hivepress.php' ) );

		new \HivePress\Components\Admin();
	}

	// todo.
	public function get_config( $name ) {
		return $this->config[ $name ];
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

	/**
	 * Gets component instance.
	 *
	 * @param string $name Component name.
	 * @return mixed
	 */
	public function __get( $name ) {
		return hp_get_array_value( $this->components, $name );
	}
}
