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
	protected static $instance;

	/**
	 * Array of HivePress extensions.
	 *
	 * @var array
	 */
	protected $extensions = [];

	/**
	 * Array of HivePress configurations.
	 *
	 * @var array
	 */
	protected $configs = [];

	/**
	 * Array of HivePress objects.
	 *
	 * @var array
	 */
	protected $objects = [];

	/**
	 * Array of HivePress classes.
	 *
	 * @var array
	 */
	protected $classes = [];

	// Forbid cloning and duplicating instances.
	protected function __clone() {}
	protected function __wakeup() {}

	/**
	 * Class constructor.
	 */
	protected function __construct() {

		// Autoload classes.
		spl_autoload_register( [ $this, 'autoload' ] );

		// Activate HivePress.
		register_activation_hook( HP_FILE, [ __CLASS__, 'activate' ] );

		// Deactivate HivePress.
		register_deactivation_hook( HP_FILE, [ __CLASS__, 'deactivate' ] );

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

			foreach ( $this->get_paths() as $dir ) {
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
		if ( get_option( 'hp_core_activated' ) || count( $this->extensions ) !== absint( get_option( 'hp_extensions_number' ) ) ) {

			/**
			 * Fires on HivePress activation.
			 *
			 * @action /activate
			 * @description Fires on HivePress activation.
			 */
			do_action( 'hivepress/v1/activate' );

			// Unset activation flag.
			if ( get_option( 'hp_core_activated' ) ) {
				update_option( 'hp_core_activated', '' );
			}

			// Update extensions number.
			if ( count( $this->extensions ) !== absint( get_option( 'hp_extensions_number' ) ) ) {
				update_option( 'hp_extensions_number', count( $this->extensions ) );
			}
		}

		if ( ! get_option( 'hp_core_version' ) || version_compare( get_option( 'hp_core_version' ), hivepress()->get_version(), '<' ) ) {

			/**
			 * Fires on HivePress update.
			 *
			 * @action /update
			 * @description Fires on HivePress update.
			 * @param string $version Old version.
			 */
			do_action( 'hivepress/v1/update', get_option( 'hp_core_version' ) );

			// Update HivePress version.
			update_option( 'hp_core_version', hivepress()->get_version() );
		}
	}

	/**
	 * Setups HivePress.
	 */
	public function setup() {

		// Setup extensions.
		$this->setup_extensions();

		// Include helpers.
		require_once hivepress()->get_path() . '/includes/helpers.php';

		// Load textdomains.
		$this->load_textdomains();

		// Initialize components.
		$this->get_components();
	}

	/**
	 * Setups extensions.
	 */
	protected function setup_extensions() {

		/**
		 * Filters HivePress extensions.
		 *
		 * @filter /extensions
		 * @description Filters HivePress extensions.
		 * @param array $extensions Extension paths or arguments. If you add a new directory path HivePress will treat it like an extension.
		 */
		$extensions = apply_filters( 'hivepress/v1/extensions', [ dirname( HP_FILE ) ] );

		foreach ( $extensions as $name => $dir ) {
			if ( is_array( $dir ) ) {

				// Add extension.
				$this->extensions[ $name ] = $dir;
			} else {

				// Get file path.
				$dirname  = basename( $dir );
				$filepath = $dir . '/' . $dirname . '.php';

				// Get extension name.
				$name = str_replace( '-', '_', preg_replace( '/^hivepress-/', '', $dirname ) );

				if ( 'hivepress' === $dirname ) {
					$name = 'core';
				}

				if ( file_exists( $filepath ) ) {

					// Get file data.
					$filedata = get_file_data(
						$filepath,
						[
							'name'    => 'Plugin Name',
							'version' => 'Version',
						]
					);

					// Add extension.
					$this->extensions[ $name ] = [
						'name'    => $filedata['name'],
						'version' => $filedata['version'],
						'path'    => $dir,
						'url'     => rtrim( plugin_dir_url( $filepath ), '/' ),
					];
				}
			}
		}
	}

	/**
	 * Loads textdomains.
	 */
	protected function load_textdomains() {
		foreach ( $this->get_paths() as $dir ) {
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

			// Get property name.
			$property = substr( $name, strlen( 'get_' ) );

			if ( in_array( $property, [ 'name', 'version', 'path', 'url' ], true ) ) {

				// Get extension name.
				$extension = 'core';

				if ( $args ) {
					$extension = reset( $args );
				}

				// Get property value.
				$value = null;

				if ( isset( $this->extensions[ $extension ][ $property ] ) ) {
					$value = $this->extensions[ $extension ][ $property ];
				}

				return $value;
			} else {

				// Set object type.
				$object_type = $property;

				if ( ! isset( $this->objects[ $object_type ] ) ) {
					$this->objects[ $object_type ] = [];

					foreach ( $this->get_paths() as $dir ) {
						foreach ( glob( $dir . '/includes/' . $object_type . '/*.php' ) as $filepath ) {

							// Get object name.
							$object_name = str_replace( '-', '_', preg_replace( '/^class-/', '', basename( $filepath, '.php' ) ) );

							if ( ! isset( $this->objects[ $object_type ][ $object_name ] ) ) {

								// Create object.
								$object = hp\create_class_instance( '\HivePress\\' . $object_type . '\\' . $object_name );

								if ( $object ) {
									$this->objects[ $object_type ][ $object_name ] = $object;
								}
							}
						}
					}
				}

				return $this->objects[ $object_type ];
			}
		}

		throw new \BadMethodCallException();
	}

	/**
	 * Routes properties.
	 *
	 * @param string $name Property name.
	 * @return object
	 */
	public function __get( $name ) {

		// Get component.
		$component = hp\get_array_value( $this->get_components(), $name );

		if ( empty( $component ) ) {

			// Create component.
			$component = hp\create_class_instance( '\HivePress\Components\\' . $name );

			if ( $component ) {
				$this->objects['components'][ $name ] = $component;
			}
		}

		return $component;
	}

	/**
	 * Gets HivePress paths.
	 *
	 * @return array
	 */
	public function get_paths() {
		return array_column( $this->extensions, 'path' );
	}

	/**
	 * Gets HivePress configuration.
	 *
	 * @param string $type Configuration type.
	 * @return array
	 */
	public function get_config( $type ) {
		if ( ! isset( $this->configs[ $type ] ) ) {
			$this->configs[ $type ] = [];

			foreach ( $this->get_paths() as $dir ) {
				$filepath = $dir . '/includes/configs/' . hp\sanitize_slug( $type ) . '.php';

				if ( file_exists( $filepath ) ) {
					$this->configs[ $type ] = hp\merge_arrays( $this->configs[ $type ], include $filepath );
				}
			}

			/**
			 * Filters HivePress configuration.
			 *
			 * @filter /{$config}
			 * @description Filters HivePress configuration.
			 * @param string $config Configuration type. Possible values: "image_sizes", "meta_boxes", "post_types", "scripts", "settings", "styles", "taxonomies", "strings".
			 * @param array $args Configuration arguments.
			 */
			$this->configs[ $type ] = apply_filters( 'hivepress/v1/' . $type, $this->configs[ $type ] );
		}

		return $this->configs[ $type ];
	}

	/**
	 * Gets HivePress classes.
	 *
	 * @param string $type Class type.
	 * @return array
	 */
	public function get_classes( $type ) {
		if ( ! isset( $this->classes[ $type ] ) ) {
			$this->classes[ $type ] = [];

			foreach ( $this->get_paths() as $dir ) {
				foreach ( glob( $dir . '/includes/' . $type . '/*.php' ) as $filepath ) {

					// Get name.
					$name = str_replace( '-', '_', preg_replace( '/^class-/', '', basename( $filepath, '.php' ) ) );

					// Get class.
					$class = '\HivePress\\' . $type . '\\' . $name;

					if ( class_exists( $class ) ) {
						$this->classes[ $type ][ $name ] = $class;
					}
				}
			}
		}

		return $this->classes[ $type ];
	}
}
