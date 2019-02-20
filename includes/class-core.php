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
	 * Array of HivePress directories.
	 *
	 * @var array
	 */
	private $dirs = [];

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
		$this->dirs[] = 'C:\xampp\htdocs\hivepress\wp-content\plugins\hivepress';

		// Define constants.
		foreach ( $this->dirs as $dir ) {
			$basename = basename( $dir );
			$filepath = $dir . '/' . $basename . '.php';
			$prefix   = 'HP_' . strtoupper( str_replace( '-', '_', trim( str_replace( 'hivepress', '', $basename ), '-' ) ) ) . '_';

			if ( 'hivepress' === $basename ) {
				$prefix = 'HP_CORE_';
			}

			if ( file_exists( $filepath ) ) {
				$data = get_file_data(
					$filepath,
					[
						'name'    => 'Plugin Name',
						'version' => 'Version',
					]
				);

				if ( ! defined( $prefix . 'NAME' ) ) {
					define( $prefix . 'NAME', $data['name'] );
				}

				if ( ! defined( $prefix . 'VERSION' ) ) {
					define( $prefix . 'VERSION', $data['version'] );
				}

				if ( ! defined( $prefix . 'DIR' ) ) {
					define( $prefix . 'DIR', $dir );
				}

				if ( ! defined( $prefix . 'URL' ) ) {
					define( $prefix . 'URL', rtrim( plugin_dir_url( $filepath ), '/' ) );
				}
			}
		}

		// Include helper functions.
		require_once HP_CORE_DIR . '/includes/helpers.php';

		// Load translation files.
		foreach ( $this->dirs as $dir ) {
			$basename   = basename( $dir );
			$textdomain = sanitize_title( $basename );

			load_plugin_textdomain( $textdomain, false, $basename . '/languages' );
		}

		$this->config = include 'C:\xampp\htdocs\hivepress\wp-content\plugins\hivepress\includes\config.php';

		new \HivePress\Components\Admin();
		new \HivePress\Components\Media();
		new \HivePress\Components\Debug();
		new \HivePress\Components\Template();
		new \HivePress\Components\Email();
		new \HivePress\Components\Form();
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
