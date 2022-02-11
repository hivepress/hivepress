<?php
/**
 * Asset component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles static assets.
 */
final class Asset extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Add image sizes.
		add_action( 'after_setup_theme', [ $this, 'add_image_sizes' ] );

		// Enqueue styles.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 5 );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 5 );

		// Add script attributes.
		add_filter( 'script_loader_tag', [ $this, 'add_script_attributes' ], 10, 2 );

		parent::__construct( $args );
	}

	/**
	 * Adds image sizes.
	 */
	public function add_image_sizes() {
		foreach ( hivepress()->get_config( 'image_sizes' ) as $name => $args ) {
			$args = get_option( hp\prefix( 'image_size_' . $name ), $args );

			add_image_size(
				hp\prefix( $name ),
				hp\get_array_value( $args, 'width', 0 ),
				hp\get_array_value( $args, 'height', 0 ),
				hp\get_array_value( $args, 'crop', false )
			);
		}
	}

	/**
	 * Enqueues styles.
	 */
	public function enqueue_styles() {

		// Get styles.
		$styles = hivepress()->get_config( 'styles' );

		// Get route.
		$route = hivepress()->router->get_current_route_name();

		// Filter styles.
		$styles = array_filter(
			$styles,
			function( $style ) use ( $route ) {
				$scope = (array) hp\get_array_value( $style, 'scope', 'frontend' );

				return ( in_array( 'frontend', $scope, true ) && ! is_admin() ) || ( in_array( 'backend', $scope, true ) && is_admin() ) || in_array( $route, $scope, true );
			}
		);

		// Enqueue styles.
		foreach ( $styles as $style ) {
			wp_enqueue_style( $style['handle'], $style['src'], hp\get_array_value( $style, 'deps', [] ), hp\get_array_value( $style, 'version', hivepress()->get_version() ) );
		}
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {

		// Get scripts.
		$scripts = hivepress()->get_config( 'scripts' );

		// Get route.
		$route = hivepress()->router->get_current_route_name();

		// Filter scripts.
		$scripts = array_filter(
			$scripts,
			function( $script ) use ( $route ) {
				$scope = (array) hp\get_array_value( $script, 'scope', 'frontend' );

				return ( in_array( 'frontend', $scope, true ) && ! is_admin() ) || ( in_array( 'backend', $scope, true ) && is_admin() ) || in_array( $route, $scope, true );
			}
		);

		// Enqueue scripts.
		foreach ( $scripts as $script ) {
			wp_enqueue_script( $script['handle'], $script['src'], hp\get_array_value( $script, 'deps', [] ), hp\get_array_value( $script, 'version', hivepress()->get_version() ), hp\get_array_value( $script, 'in_footer', true ) );

			// Add script data.
			if ( isset( $script['data'] ) ) {
				wp_localize_script( $script['handle'], lcfirst( str_replace( ' ', '', ucwords( str_replace( '-', ' ', $script['handle'] ) ) ) ) . 'Data', $script['data'] );
			}
		}
	}

	/**
	 * Adds script attributes.
	 *
	 * @param string $tag Script tag.
	 * @param string $handle Script handle.
	 * @return string
	 */
	public function add_script_attributes( $tag, $handle ) {

		// Set attributes.
		$attributes = [ 'async', 'defer', 'crossorigin' ];

		// Filter HTML.
		foreach ( $attributes as $attribute ) {
			$value = wp_scripts()->get_data( $handle, $attribute );

			if ( $value ) {
				$output = ' ' . $attribute;

				if ( strpos( $tag, $output . '>' ) === false && strpos( $tag, $output . ' ' ) === false && strpos( $tag, $output . '="' ) === false ) {
					if ( ! is_bool( $value ) ) {
						$output .= '="' . esc_attr( $value ) . '"';
					}

					$tag = str_replace( '></', $output . '></', $tag );
				}
			}
		}

		return $tag;
	}
}
