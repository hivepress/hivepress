<?php
namespace HivePress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract component class.
 *
 * @class Component
 */
abstract class Component {

	/**
	 * Component name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Array of the component settings.
	 *
	 * @var array
	 */
	protected $settings = [];

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings = [] ) {

		// Set name.
		$this->name = strtolower( ( new \ReflectionClass( $this ) )->getShortName() );

		// Assign settings.
		$this->settings = $settings;

		// Initialize settings.
		add_action( 'after_setup_theme', [ $this, 'init_settings' ] );
	}

	/**
	 * Initializes settings.
	 */
	public function init_settings() {

		// Get component name.
		$component_name = $this->name;

		foreach ( $this->settings as $settings_type => $settings ) {

			// Filter settings.
			$this->settings[ $settings_type ] = apply_filters( "hivepress/{$component_name}/{$settings_type}", $settings );

			// Add actions to initialize settings.
			switch ( $settings_type ) {
				case 'post_types':
				case 'taxonomies':
				case 'image_sizes':
					add_action( 'init', [ $this, 'init_' . $settings_type ] );

					break;

				case 'admin_styles':
				case 'admin_scripts':
					add_action( 'admin_enqueue_scripts', [ $this, 'init_' . $settings_type ] );

					break;

				case 'styles':
				case 'scripts':
					add_action( 'wp_enqueue_scripts', [ $this, 'init_' . $settings_type ] );

					break;
			}

			// Fires when settings are being initialized.
			do_action( "hivepress/component/init_{$settings_type}", $this->settings[ $settings_type ], $this->name );
		}
	}

	/**
	 * Routes component functions.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __call( $name, $args ) {
		if ( strpos( $name, 'init_' ) === 0 ) {

			// Get settings type.
			$settings_type = str_replace( 'init_', '', $name );

			// Initialize settings.
			foreach ( hp_get_array_value( $this->settings, $settings_type, [] ) as $setting_id => $setting ) {
				switch ( $settings_type ) {

					// Post types.
					case 'post_types':
						register_post_type( hp_prefix( $setting_id ), $setting );

						break;

					// Taxonomies.
					case 'taxonomies':
						register_taxonomy( hp_prefix( $setting_id ), hp_prefix( $setting['object_type'] ), $setting['args'] );

						break;

					// Image sizes.
					case 'image_sizes':
						add_image_size( hp_prefix( $this->name . '__' . $setting_id ), $setting['width'], hp_get_array_value( $setting, 'height', 9999 ), hp_get_array_value( $setting, 'crop', false ) );

						break;

					// Styles.
					case 'admin_styles':
					case 'styles':
						$this->enqueue_style( $setting );

						break;

					// Scripts.
					case 'admin_scripts':
					case 'scripts':
						$this->enqueue_script( $setting );

						break;
				}
			}
		}
	}

	/**
	 * Enqueues CSS style.
	 *
	 * @param array $args
	 */
	protected function enqueue_style( $args ) {
		if ( isset( $args['src'] ) ) {
			wp_enqueue_style( $args['handle'], $args['src'], hp_get_array_value( $args, 'deps', [] ), hp_get_array_value( $args, 'version', HP_CORE_VERSION ) );
		} else {
			wp_enqueue_style( $args['handle'] );
		}
	}

	/**
	 * Enqueues JS script.
	 *
	 * @param array $args
	 */
	protected function enqueue_script( $args ) {
		if ( isset( $args['src'] ) ) {
			wp_enqueue_script( $args['handle'], $args['src'], hp_get_array_value( $args, 'deps', [] ), hp_get_array_value( $args, 'version', HP_CORE_VERSION ), hp_get_array_value( $args, 'in_footer', true ) );

			// Load script data.
			if ( isset( $args['data'] ) ) {
				wp_localize_script( $args['handle'], lcfirst( str_replace( ' ', '', ucwords( str_replace( '-', ' ', $args['handle'] ) ) ) ) . 'Data', $args['data'] );
			}
		} else {
			wp_enqueue_script( $args['handle'], '', [], hp_get_array_value( $args, 'version', false ), hp_get_array_value( $args, 'in_footer', true ) );
		}
	}
}
