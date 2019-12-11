<?php
/**
 * HivePress core.
 *
 * @package HivePress
 */

namespace HivePress;

use HivePress\Helpers as hp;

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
	 * Array of HivePress configurations.
	 *
	 * @var array
	 */
	private $configs = [];

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

		// Deactivate HivePress.
		register_deactivation_hook( HP_CORE_FILE, [ __CLASS__, 'deactivate' ] );

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

					if ( ! ( new \ReflectionClass( $class ) )->isAbstract() && method_exists( $class, 'init' ) && ( new \ReflectionMethod( $class, 'init' ) )->isStatic() ) {
						call_user_func( [ $class, 'init' ] );
					}

					break;
				}
			}
		}
	}

	/**
	 * Activates HivePress.
	 */
	public static function activate() {

		// Set activation flag.
		update_option( 'hp_core_activated', '1' );
	}

	/**
	 * Deactivates HivePress.
	 */
	public static function deactivate() {

		/**
		 * Fires on HivePress deactivation.
		 *
		 * @action /deactivate
		 * @description Fires on HivePress deactivation.
		 */
		do_action( 'hivepress/v1/deactivate' );
	}

	/**
	 * Installs HivePress.
	 */
	public function install() {
		if ( get_option( 'hp_core_activated' ) || count( $this->dirs ) !== absint( get_option( 'hp_dirs_number' ) ) ) {

			/**
			 * Fires on HivePress activation.
			 *
			 * @action /activate
			 * @description Fires on HivePress activation.
			 */
			do_action( 'hivepress/v1/activate' );

			// Unset activation flag.
			if ( get_option( 'hp_core_activated' ) ) {
				update_option( 'hp_core_activated', '0' );
			}

			// Update directories number.
			if ( count( $this->dirs ) !== absint( get_option( 'hp_dirs_number' ) ) ) {
				update_option( 'hp_dirs_number', count( $this->dirs ) );
			}
		}

		if ( ! get_option( 'hp_core_version' ) || version_compare( get_option( 'hp_core_version' ), HP_CORE_VERSION, '<' ) ) {

			/**
			 * Fires on HivePress update.
			 *
			 * @action /update
			 * @description Fires on HivePress update.
			 */
			do_action( 'hivepress/v1/update' );

			// Update HivePress version.
			update_option( 'hp_core_version', HP_CORE_VERSION );
		}
	}

	/**
	 * Setups HivePress.
	 */
	public function setup() {

		/**
		 * Filters HivePress directories.
		 *
		 * @filter /dirs
		 * @description Filters HivePress directories.
		 * @param array $dirs Directory paths. If you add a new path HivePress will treat it like an extension.
		 */
		$this->dirs = apply_filters( 'hivepress/v1/dirs', [ dirname( HP_CORE_FILE ) ] );

		// Define constants.
		$this->define_constants();

		// Load helper functions.
		require_once HP_CORE_DIR . '/includes/helpers.php';

		// Load textdomains.
		$this->load_textdomains();

		// Initialize components.
		$this->get_components();
	}

	/**
	 * Defines constants.
	 */
	private function define_constants() {
		foreach ( $this->dirs as $dir ) {
			$dirname  = basename( $dir );
			$filepath = $dir . '/' . $dirname . '.php';
			$prefix   = 'HP_' . strtoupper( str_replace( '-', '_', preg_replace( '/^hivepress-/', '', $dirname ) ) ) . '_';

			if ( 'hivepress' === $dirname ) {
				$prefix = 'HP_CORE_';
			}

			if ( file_exists( $filepath ) ) {
				$filedata = get_file_data(
					$filepath,
					[
						'name'    => 'Plugin Name',
						'version' => 'Version',
					]
				);

				if ( ! defined( $prefix . 'NAME' ) ) {
					define( $prefix . 'NAME', $filedata['name'] );
				}

				if ( ! defined( $prefix . 'VERSION' ) ) {
					define( $prefix . 'VERSION', $filedata['version'] );
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
			$dirname    = basename( $dir );
			$textdomain = hp\sanitize_slug( $dirname );

			load_plugin_textdomain( $textdomain, false, $dirname . '/languages' );
		}
	}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @throws \BadMethodCallException Invalid method.
	 * @return array
	 */
	public function __call( $name, $args ) {
		if ( strpos( $name, 'get_' ) === 0 ) {

			// Get object type.
			$object_type = substr( $name, strlen( 'get_' ) );

			if ( ! isset( $this->objects[ $object_type ] ) ) {
				$this->objects[ $object_type ] = [];

				foreach ( $this->dirs as $dir ) {
					foreach ( glob( $dir . '/includes/' . $object_type . '/*.php' ) as $filepath ) {

						// Get object name.
						$object_name = str_replace( '-', '_', preg_replace( '/^class-/', '', basename( $filepath, '.php' ) ) );

						// Create object.
						$object = hp\create_class_instance( '\HivePress\\' . $object_type . '\\' . $object_name );

						if ( ! is_null( $object ) ) {
							$this->objects[ $object_type ][ $object_name ] = $object;
						}
					}
				}
			}

			return $this->objects[ $object_type ];
		}

		throw new \BadMethodCallException();
	}

	/**
	 * Routes properties.
	 *
	 * @param string $name Property name.
	 * @throws \UnexpectedValueException Invalid property.
	 * @return object
	 */
	public function __get( $name ) {
		if ( isset( $this->get_components()[ $name ] ) ) {
			return $this->get_components()[ $name ];
		}

		throw new \UnexpectedValueException();
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
	 * Gets HivePress configuration.
	 *
	 * @param string $name Configuration name.
	 * @return array
	 */
	public function get_config( $name ) {
		if ( ! isset( $this->configs[ $name ] ) ) {
			$this->configs[ $name ] = [];

			foreach ( $this->dirs as $dir ) {
				$filepath = $dir . '/includes/configs/' . hp\sanitize_slug( $name ) . '.php';

				if ( file_exists( $filepath ) ) {
					$this->configs[ $name ] = hp\merge_arrays( $this->configs[ $name ], include $filepath );
				}
			}

			/**
			 * Filters HivePress configuration.
			 *
			 * @filter /{$config}
			 * @description Filters HivePress configuration.
			 * @param string $config Configuration type. Possible values: "image_sizes", "meta_boxes", "post_types", "scripts", "settings", "styles", "taxonomies".
			 * @param array $args Configuration arguments.
			 */
			$this->configs[ $name ] = apply_filters( 'hivepress/v1/' . $name, $this->configs[ $name ] );
		}

		return $this->configs[ $name ];
	}
}
