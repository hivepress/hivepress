<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @class HivePress
 */
final class HivePress {

	/**
	 * The single instance of the class.
	 *
	 * @var HivePress
	 */
	private static $instance;

	/**
	 * Array of the plugin paths.
	 *
	 * @var array
	 */
	private static $plugin_paths = [];

	/**
	 * Array of the component instances.
	 *
	 * @var array
	 */
	private $components = [];

	// Forbid duplicating plugin instance.
	private function __clone() {}
	private function __wakeup() {}

	/**
	 * Class constructor.
	 */
	private function __construct() {

		if ( is_admin() ) {

			// Activate plugin.
			register_activation_hook( HP_CORE_FILE, [ __CLASS__, 'activate_plugin' ] );

			// Install plugin.
			add_action( 'init', [ $this, 'install_plugin' ] );

			// Uninstall plugin.
			register_uninstall_hook( HP_CORE_FILE, [ __CLASS__, 'uninstall_plugin' ] );
		}

		// Setup plugin.
		add_action( 'plugins_loaded', [ $this, 'setup_plugin' ] );
	}

	/**
	 * Ensures only one plugin instance is loaded.
	 *
	 * @see hivepress()
	 * @return HivePress
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initializes plugin.
	 */
	private static function init_plugin() {

		// Include helpers.
		require_once dirname( HP_CORE_FILE ) . '/includes/helpers.php';

		// Filter plugin paths.
		self::$plugin_paths = apply_filters( 'hivepress/core/plugin_paths', [ dirname( HP_CORE_FILE ) ] );

		// Define constants.
		foreach ( self::$plugin_paths as $plugin_path ) {
			$plugin_basename = plugin_basename( $plugin_path );
			$plugin_file     = $plugin_path . '/' . $plugin_basename . '.php';
			$plugin_prefix   = strtoupper( hp_prefix( str_replace( '-', '_', trim( str_replace( 'hivepress', '', $plugin_basename ), '-' ) ) ) ) . '_';

			if ( 'hivepress' === $plugin_basename ) {
				$plugin_prefix = strtoupper( hp_prefix( 'core' ) ) . '_';
			}

			if ( file_exists( $plugin_file ) ) {
				if ( 'hivepress' !== $plugin_basename ) {
					if ( ! defined( $plugin_prefix . 'FILE' ) ) {
						define( $plugin_prefix . 'FILE', $plugin_file );
					}
				}

				$plugin_data = get_file_data(
					constant( $plugin_prefix . 'FILE' ),
					[
						'name'    => 'Plugin Name',
						'version' => 'Version',
					]
				);

				if ( ! defined( $plugin_prefix . 'NAME' ) ) {
					define( $plugin_prefix . 'NAME', $plugin_data['name'] );
				}

				if ( ! defined( $plugin_prefix . 'VERSION' ) ) {
					define( $plugin_prefix . 'VERSION', $plugin_data['version'] );
				}

				if ( ! defined( $plugin_prefix . 'PATH' ) ) {
					define( $plugin_prefix . 'PATH', dirname( constant( $plugin_prefix . 'FILE' ) ) );
				}

				if ( ! defined( $plugin_prefix . 'URL' ) ) {
					define( $plugin_prefix . 'URL', rtrim( plugin_dir_url( constant( $plugin_prefix . 'FILE' ) ), '/' ) );
				}
			}
		}
	}

	/**
	 * Activates plugin.
	 */
	public static function activate_plugin() {

		// Add activation flag.
		add_option( 'hp_core_activated', '1' );

		// Add plugins number.
		add_option( 'hp_plugins_number', count( self::$plugin_paths ) );
	}

	/**
	 * Installs plugin.
	 */
	public function install_plugin() {
		if ( get_option( 'hp_core_activated' ) || count( self::$plugin_paths ) !== absint( get_option( 'hp_plugins_number' ) ) ) {

			// Fires once the plugin is activated.
			do_action( 'hivepress/core/activate_plugin' );

			if ( get_option( 'hp_core_activated' ) ) {

				// Save the plugin version.
				add_option( 'hp_core_version', HP_CORE_VERSION );

				// Delete activation flag.
				delete_option( 'hp_core_activated' );
			}

			if ( count( self::$plugin_paths ) !== absint( get_option( 'hp_plugins_number' ) ) ) {

				// Update plugins number.
				update_option( 'hp_plugins_number', count( self::$plugin_paths ) );
			}
		}
	}

	/**
	 * Uninstalls plugin.
	 */
	public static function uninstall_plugin() {

		// Initialize plugin.
		self::init_plugin();

		// Get plugin settings.
		$plugin_settings = self::get_plugin_settings();

		// Delete plugin data.
		foreach ( $plugin_settings as $component => $component_settings ) {
			foreach ( $component_settings as $settings_type => $settings ) {
				switch ( $settings_type ) {

					// Options.
					case 'options':
						foreach ( $settings as $tab ) {
							foreach ( $tab['sections'] as $section ) {
								$options = hp_prefix( array_keys( hp_get_array_value( $section, 'fields', [] ) ) );

								foreach ( $options as $option ) {
									delete_option( $option );
								}
							}
						}

						break;

					// Posts.
					case 'post_types':
						$post_ids = get_posts(
							[
								'post_type'      => hp_prefix( array_keys( $settings ) ),
								'post_status'    => 'any',
								'posts_per_page' => -1,
								'fields'         => 'ids',
							]
						);

						foreach ( $post_ids as $post_id ) {
							wp_delete_post( $post_id, true );
						}

						break;

					// Terms.
					case 'taxonomies':
						$taxonomies = hp_prefix( array_keys( $settings ) );

						foreach ( $taxonomies as $taxonomy ) {
							register_taxonomy( $taxonomy, 'post' );
						}

						$terms = get_terms(
							[
								'taxonomy'   => $taxonomies,
								'hide_empty' => false,
							]
						);

						foreach ( $terms as $term ) {
							wp_delete_term( $term->term_id, $term->taxonomy );
						}

						break;
				}
			}
		}
	}

	/**
	 * Setups plugin.
	 */
	public function setup_plugin() {

		// Initialize plugin.
		self::init_plugin();

		// Load translation files.
		foreach ( self::$plugin_paths as $plugin_path ) {
			$plugin_basename   = plugin_basename( $plugin_path );
			$plugin_textdomain = sanitize_title( $plugin_basename );

			load_plugin_textdomain( $plugin_textdomain, false, $plugin_basename . '/languages' );
		}

		// Initialize components.
		$this->init_components();
	}

	/**
	 * Gets plugin paths.
	 *
	 * @return array
	 */
	public function get_plugin_paths() {
		return self::$plugin_paths;
	}

	/**
	 * Gets plugin settings.
	 *
	 * @return array
	 */
	private static function get_plugin_settings() {
		$plugin_settings = [];

		foreach ( self::$plugin_paths as $plugin_path ) {
			$settings_path = $plugin_path . '/includes/settings.php';

			if ( file_exists( $settings_path ) ) {
				require_once $settings_path;

				$plugin_settings = hp_merge_arrays( $plugin_settings, $settings );
			}
		}

		return $plugin_settings;
	}

	/**
	 * Includes components.
	 *
	 * @param string $path
	 */
	private function include_components( $path ) {
		foreach ( self::$plugin_paths as $plugin_path ) {
			$file_paths = glob( $plugin_path . '/' . $path . '/*.php' );

			if ( is_array( $file_paths ) ) {
				foreach ( $file_paths as $file_path ) {
					require_once $file_path;
				}
			}
		}
	}

	/**
	 * Initializes components.
	 */
	private function init_components() {

		// Get plugin settings.
		$plugin_settings = self::get_plugin_settings();

		// Include abstract classes.
		require_once HP_CORE_PATH . '/includes/components/abstracts/class-component.php';

		$this->include_components( 'includes/components/abstracts' );

		// Include component classes.
		$all_classes = get_declared_classes();

		$this->include_components( 'includes/components' );

		// Initialize component classes.
		$component_classes = array_diff( get_declared_classes(), $all_classes );

		foreach ( $component_classes as $component_class ) {
			$component_path = explode( '\\', $component_class );
			$component_name = str_replace( '-', '_', sanitize_title( end( $component_path ) ) );

			if ( ! isset( $this->components[ $component_name ] ) ) {
				$this->components[ $component_name ] = new $component_class( hp_get_array_value( $plugin_settings, $component_name, [] ) );
			}
		}
	}

	/**
	 * Gets component instance.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		return hp_get_array_value( $this->components, $name );
	}
}
