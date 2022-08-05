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
 */
final class Core {

	/**
	 * The single class instance.
	 *
	 * @var Core
	 */
	protected static $instance;

	/**
	 * HivePress extensions.
	 *
	 * @var array
	 */
	protected $extensions = [];

	/**
	 * HivePress configurations.
	 *
	 * @var array
	 */
	protected $configs = [];

	/**
	 * HivePress objects.
	 *
	 * @var array
	 */
	protected $objects = [];

	/**
	 * HivePress classes.
	 *
	 * @var array
	 */
	protected $classes = [];

	/**
	 * Forbid cloning instances.
	 */
	protected function __clone() {}

	/**
	 * Forbids unserializing instances.
	 *
	 * @throws \BadMethodCallException Invalid method.
	 */
	public function __wakeup() {
		throw new \BadMethodCallException();
	}

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
		add_action( 'init', [ $this, 'install' ], 10000 );

		// Setup HivePress.
		add_action( 'plugins_loaded', [ $this, 'setup' ], -10 );
	}

	/**
	 * Returns the single class instance.
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
	 * Autoloads HivePress classes.
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
	 * Activates HivePress plugin.
	 */
	public static function activate() {

		// Set activation flag.
		update_option( 'hp_core_activated', '1' );
	}

	/**
	 * Deactivates HivePress plugin.
	 */
	public static function deactivate() {

		/**
		 * Fires when HivePress plugin is deactivated.
		 *
		 * @hook hivepress/v1/deactivate
		 */
		do_action( 'hivepress/v1/deactivate' );
	}

	/**
	 * Installs HivePress plugin.
	 */
	public function install() {
		if ( get_option( 'hp_core_activated' ) || count( $this->extensions ) !== absint( get_option( 'hp_extensions_number' ) ) ) {

			/**
			 * Fires when HivePress plugin is activated.
			 *
			 * @hook hivepress/v1/activate
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

			// Set installation time.
			if ( ! get_option( 'hp_installed_time' ) ) {
				update_option( 'hp_installed_time', time() );
			}
		}

		if ( ! get_option( 'hp_core_version' ) || version_compare( get_option( 'hp_core_version' ), $this->get_version(), '<' ) ) {

			if ( get_option( 'hp_core_version' ) ) {

				/**
				 * Fires when HivePress plugin is updated.
				 *
				 * @hook hivepress/v1/update
				 * @param {string} $version Previous version.
				 */
				do_action( 'hivepress/v1/update', get_option( 'hp_core_version' ) );
			}

			// Update HivePress version.
			update_option( 'hp_core_version', $this->get_version() );
		}
	}

	/**
	 * Setups HivePress plugin.
	 */
	public function setup() {

		// Setup extensions.
		$this->setup_extensions();

		// Include helpers.
		require_once $this->get_path() . '/includes/helpers.php';

		// Load textdomains.
		$this->load_textdomains();

		// Load packages.
		$this->load_packages();

		// Initialize components.
		$this->get_components();

		/**
		 * Fires when HivePress plugin is set up.
		 *
		 * @hook hivepress/v1/setup
		 */
		do_action( 'hivepress/v1/setup' );
	}

	/**
	 * Setups HivePress extensions.
	 */
	protected function setup_extensions() {

		/**
		 * Filters the directory paths of HivePress extensions. You can register a new extension by adding its directory path to the filtered array.
		 *
		 * @hook hivepress/v1/extensions
		 * @param {array} $paths Directory paths.
		 * @return {array} Directory paths.
		 */
		$extensions = apply_filters( 'hivepress/v1/extensions', [ dirname( HP_FILE ) ] );

		// Add updater if available.
		if ( ! isset( $extensions['updates'] ) ) {
			$path = '/vendor/hivepress/hivepress-updates';

			foreach ( $extensions as $dir ) {
				if ( file_exists( $dir . $path . '/hivepress-updates.php' ) ) {
					$extensions['updates'] = $dir . $path;

					break;
				}
			}
		}

		// Add extension details.
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
	 * Loads text domains.
	 */
	protected function load_textdomains() {
		foreach ( $this->get_paths() as $dir ) {
			$dirname    = basename( $dir );
			$textdomain = hp\sanitize_slug( $dirname );

			load_plugin_textdomain( $textdomain, false, $dirname . '/languages' );
		}
	}

	/**
	 * Loads Composer packages.
	 */
	protected function load_packages() {
		foreach ( $this->get_paths() as $dir ) {
			$filepath = $dir . '/vendor/autoload.php';

			if ( file_exists( $filepath ) ) {
				require_once $filepath;
			}
		}
	}

	/**
	 * Catches calls to undefined methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @throws \BadMethodCallException Invalid method.
	 * @return mixed
	 */
	public function __call( $name, $args ) {
		if ( strpos( $name, 'get_' ) === 0 ) {

			// Get property name.
			$property = substr( $name, strlen( 'get_' ) );

			if ( in_array( $property, [ 'name', 'version', 'path', 'url' ], true ) ) {

				// Get extension name.
				$extension = 'core';

				if ( $args ) {
					$extension = hp\get_first_array_value( $args );
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

							// Create object.
							$object = hp\create_class_instance( '\HivePress\\' . $object_type . '\\' . $object_name );

							if ( $object ) {
								$this->objects[ $object_type ][ $object_name ] = $object;
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
	 * Catches getting undefined properties.
	 *
	 * @param string $name Property name.
	 * @return mixed
	 */
	public function __get( $name ) {
		return hp\get_array_value( $this->get_components(), $name );
	}

	/**
	 * Gets HivePress filepaths.
	 *
	 * @return array
	 */
	public function get_paths() {
		return array_column( $this->extensions, 'path' );
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

			foreach ( $this->get_paths() as $dir ) {
				$filepath = $dir . '/includes/configs/' . hp\sanitize_slug( $name ) . '.php';

				if ( file_exists( $filepath ) ) {
					$this->configs[ $name ] = hp\merge_arrays( $this->configs[ $name ], include $filepath );
				}
			}

			/**
			 * Filters HivePress configuration. The dynamic part of the hook refers to the configuration name (e.g. `post_types`, `taxonomies`, `meta_boxes`). You can check the available configurations in the `includes/configs` directory of HivePress.
			 *
			 * @hook hivepress/v1/{config_name}
			 * @param {array} $config Configuration array.
			 * @return {array} Configuration array.
			 */
			$this->configs[ $name ] = apply_filters( 'hivepress/v1/' . $name, $this->configs[ $name ] );
		}

		return $this->configs[ $name ];
	}

	/**
	 * Gets HivePress classes.
	 *
	 * @param string $namespace Class namespace.
	 * @return array
	 */
	public function get_classes( $namespace ) {
		if ( ! isset( $this->classes[ $namespace ] ) ) {
			$this->classes[ $namespace ] = [];

			foreach ( $this->get_paths() as $dir ) {
				foreach ( glob( $dir . '/includes/' . $namespace . '/*.php' ) as $filepath ) {

					// Get name.
					$name = str_replace( '-', '_', preg_replace( '/^class-/', '', basename( $filepath, '.php' ) ) );

					// Get class.
					$class = '\HivePress\\' . $namespace . '\\' . $name;

					if ( class_exists( $class ) && ! ( new \ReflectionClass( $class ) )->isAbstract() ) {
						$this->classes[ $namespace ][ $name ] = $class;
					}
				}
			}
		}

		return $this->classes[ $namespace ];
	}
}
