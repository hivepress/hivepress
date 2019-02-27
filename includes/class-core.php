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
	 * Array of HivePress objects.
	 *
	 * @var array
	 */
	private $objects = [];

	// Forbid cloning and duplicating instances.
	private function __clone() {}
	private function __wakeup() {}

	/**
	 * Class constructor.
	 */
	private function __construct() {

		// Autoload classes.
		spl_autoload_register( [ $this, 'autoload' ] );

		// Activate HivePress.
		register_activation_hook( HP_CORE_FILE, [ __CLASS__, 'activate' ] );

		// Install HivePress.
		add_action( 'init', [ $this, 'install' ] );

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
	public function autoload( $class ) {
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
	 * Activates HivePress.
	 */
	public static function activate() {

		// Set activation status.
		add_option( 'hp_core_activated', '1' );
	}

	/**
	 * Installs HivePress.
	 */
	public function install() {
		if ( get_option( 'hp_core_activated' ) || count( $this->dirs ) !== absint( get_option( 'hp_dirs_number' ) ) ) {

			// Fires on HivePress activation.
			do_action( 'hivepress/activate' );

			// Delete activation status.
			if ( get_option( 'hp_core_activated' ) ) {
				delete_option( 'hp_core_activated' );
			}

			// Update HivePress directories number.
			if ( count( $this->dirs ) !== absint( get_option( 'hp_dirs_number' ) ) ) {
				update_option( 'hp_dirs_number', count( $this->dirs ) );
			}
		}
	}

	/**
	 * Setups HivePress.
	 */
	public function setup() {

		// Set HivePress directories.
		$this->dirs = apply_filters( 'hivepress/dirs', [ dirname( HP_CORE_FILE ) ] );

		// Define constants.
		$this->define_constants();

		// Include helper functions.
		require_once HP_CORE_DIR . '/includes/helpers.php';

		// Load textdomains.
		$this->load_textdomains();

		// Set components.
		$this->objects['components'] = $this->get_components();
	}

	/**
	 * Defines constants.
	 */
	private function define_constants() {
		foreach ( $this->dirs as $dir ) {
			$basename = basename( $dir );
			$filepath = $dir . '/' . $basename . '.php';
			$prefix   = 'HP_' . strtoupper( str_replace( '-', '_', str_replace( 'hivepress-', '', $basename ) ) ) . '_';

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
			$textdomain = str_replace( '_', '-', $basename );

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
			$object_type = substr( $name, strlen( 'get' ) + 1 );

			if ( ! isset( $this->objects[ $object_type ] ) ) {
				$this->objects[ $object_type ] = [];

				foreach ( $this->dirs as $dir ) {
					foreach ( glob( $dir . '/includes/' . $object_type . '/*.php' ) as $filepath ) {
						$object_name  = str_replace( '-', '_', str_replace( 'class-', '', str_replace( '.php', '', basename( $filepath ) ) ) );
						$object_class = '\HivePress\\' . $object_type . '\\' . $object_name;

						if ( ! ( new \ReflectionClass( $object_class ) )->isAbstract() ) {
							$this->objects[ $object_type ][ $object_name ] = new $object_class();
						}
					}
				}
			}

			return $this->objects[ $object_type ];
		}
	}

	/**
	 * Gets HivePress directories.
	 *
	 * @return array
	 */
	public function get_dirs() {
		return $this->dirs;
	}

	/**
	 * Gets configuration.
	 *
	 * @param string $name Configuration name.
	 * @return array
	 */
	public function get_config( $name ) {
		if ( ! isset( $this->config[ $name ] ) ) {
			$config = [];

			foreach ( $this->dirs as $dir ) {
				$filepath = $dir . '/includes/configs/' . str_replace( '_', '-', $name ) . '.php';

				if ( file_exists( $filepath ) ) {
					$config = array_merge( $config, include $filepath );
				}
			}

			// Filter configuration.
			$this->config[ $name ] = apply_filters( 'hivepress/' . $name, $config );
		}

		return $this->config[ $name ];
	}
}
