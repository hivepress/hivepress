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
		spl_autoload_register( [ $this, 'autoload' ] );
	}

	// todo.
	private function autoload( $class ) {
		$parts = explode( '\\', str_replace( '_', '-', strtolower( $class ) ) );

		if ( count( $parts ) > 1 && reset( $parts ) === 'hivepress' ) {
			$filename = 'class-' . end( $parts ) . '.php';

			array_shift( $parts );
			array_pop( $parts );

			foreach ( $this->dirs as $dir ) {
				$filepath = rtrim( $dir . '/includes/' . implode( '/', $parts ), '/' ) . '/' . $filename;

				if ( file_exists( $filepath ) ) {
					require_once $filepath;
				}
			}
		}
	}

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

		$this->components = $this->get_components();
	}

	// todo.
	public function get_config( $name ) {
		if ( ! isset( $this->config[ $name ] ) ) {
			$this->config[ $name ] = include HP_CORE_DIR . '/includes/configs/' . str_replace( '_', '-', $name ) . '.php';
		}

		return hp_get_array_value( $this->config, $name );
	}

	// todo
	public function __call( $name, $args ) {
		if ( strpos( $name, 'get_' ) === 0 ) {
			$type  = substr( $name, strlen( 'get' ) + 1 );
			$items = [];

			foreach ( $this->dirs as $dir ) {
				foreach ( glob( $dir . '/includes/' . $type . '/*.php' ) as $filepath ) {
					$id    = str_replace( '-', '_', str_replace( 'class-', '', str_replace( '.php', '', basename( $filepath ) ) ) );
					$class = '\HivePress\\' . $type . '\\' . $id;

					if ( ! ( new \ReflectionClass( $class ) )->isAbstract() ) {
						$items[ $id ] = new $class();
					}
				}
			}

			return $items;
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
