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

		// Autoload classes.
		spl_autoload_register( [ $this, 'autoload' ] );

		// Setup HivePress.
		add_action( 'plugins_loaded', [ $this, 'setup' ] );
	}

	/**
	 * Ensures only one instance is loaded.
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
	 * Autoloads classes.
	 *
	 * @param string $class Class name.
	 */
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

	/**
	 * Setups HivePress.
	 */
	public function setup() {

		// Set the core directory.
		$this->dirs[] = dirname( HP_CORE_FILE );

		// Define constants.
		$this->define_constants();

		// Include helper functions.
		require_once HP_CORE_DIR . '/includes/helpers.php';

		// Load textdomains.
		$this->load_textdomains();

		// Set components.
		$this->components = $this->get_components();
	}

	/**
	 * Defines constants.
	 */
	private function define_constants() {
		// todo.
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
	}

	/**
	 * Loads textdomains.
	 */
	private function load_textdomains() {
		foreach ( $this->dirs as $dir ) {
			$basename   = basename( $dir );
			$textdomain = sanitize_title( $basename );

			load_plugin_textdomain( $textdomain, false, $basename . '/languages' );
		}
	}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 */
	public function __call( $name, $args ) {
		if ( strpos( $name, 'get_' ) === 0 ) {
			$instances = [];

			$instance_type = substr( $name, strlen( 'get' ) + 1 );

			foreach ( $this->dirs as $dir ) {
				foreach ( glob( $dir . '/includes/' . $instance_type . '/*.php' ) as $filepath ) {
					$instance_name  = str_replace( '-', '_', str_replace( 'class-', '', str_replace( '.php', '', basename( $filepath ) ) ) );
					$instance_class = '\HivePress\\' . $instance_type . '\\' . $instance_name;

					if ( ! ( new \ReflectionClass( $instance_class ) )->isAbstract() ) {
						$instances[ $instance_name ] = new $instance_class();
					}
				}
			}

			return $instances;
		}
	}

	/**
	 * Gets configuration.
	 *
	 * @param string $name Configuration name.
	 * @return array
	 */
	public function get_config( $name ) {
		if ( ! isset( $this->config[ $name ] ) ) {
			$this->config[ $name ] = [];

			foreach ( $this->dirs as $dir ) {
				$filepath = $dir . '/includes/configs/' . str_replace( '_', '-', $name ) . '.php';

				if ( file_exists( $filepath ) ) {
					$config = include $filepath;

					$this->config[ $name ] = array_merge( $this->config[ $name ], $config );
				}
			}
		}

		return $this->config[ $name ];
	}

	/**
	 * Gets property.
	 *
	 * @param string $name Property name.
	 * @return mixed
	 */
	public function __get( $name ) {
		return hp_get_array_value( $this->components, $name );
	}
}
