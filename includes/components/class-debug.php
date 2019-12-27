<?php
/**
 * Debug component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Debug component class.
 *
 * @class Debug
 */
final class Debug extends Component {

	/**
	 * Array of styles.
	 *
	 * @var array
	 */
	protected $styles = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Check debug status.
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Alter styles.
		add_filter( 'hivepress/v1/styles', [ $this, 'alter_styles' ] );
		add_filter( 'hivetheme/v1/styles', [ $this, 'alter_styles' ] );

		// Enqueue styles.
		add_action( 'wp_head', [ $this, 'enqueue_styles' ], 999 );
		add_action( 'admin_head', [ $this, 'enqueue_styles' ], 999 );

		// Alter scripts.
		add_filter( 'hivepress/v1/scripts', [ $this, 'alter_scripts' ] );
		add_filter( 'hivetheme/v1/scripts', [ $this, 'alter_scripts' ] );

		parent::__construct( $args );
	}

	/**
	 * Checks debug status.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return defined( 'HP_DEBUG' ) && HP_DEBUG;
	}

	/**
	 * Alters styles.
	 *
	 * @param array $styles Styles.
	 * @return array
	 */
	public function alter_styles( $styles ) {

		// Filter styles.
		$styles = array_filter(
			$styles,
			function( $style ) {
				$scope = (array) hp\get_array_value( $style, 'scope' );

				return ! array_diff( [ 'frontend', 'backend' ], $scope ) || ( ! is_admin() xor in_array( 'backend', $scope, true ) );
			}
		);

		// Alter styles.
		foreach ( $styles as $name => $style ) {
			$parts = explode( '/', hp\get_array_value( $style, 'src' ) );

			if ( in_array( end( $parts ), [ 'style.css', 'frontend.min.css', 'backend.min.css' ], true ) ) {
				$this->styles[] = $style;

				unset( $styles[ $name ] );
			}
		}

		return $styles;
	}

	/**
	 * Enqueues styles.
	 */
	public function enqueue_styles() {
		$output = '';

		// Enqueue styles.
		foreach ( $this->styles as $style ) {
			$url = preg_replace( '/(\.min)?\.css$/', '.less', $style['src'] );

			$output .= '<link rel="stylesheet/less" type="text/css" href="' . esc_url( $url ) . '">';
		}

		// Enqueue LESS.
		$output .= '<script src="//cdnjs.cloudflare.com/ajax/libs/less.js/3.9.0/less.min.js"></script>';

		echo $output;
	}

	/**
	 * Alters scripts.
	 *
	 * @param array $scripts Scripts.
	 * @return array
	 */
	public function alter_scripts( $scripts ) {
		foreach ( $scripts as $name => $script ) {
			$parts = explode( '/', hp\get_array_value( $script, 'src' ) );

			if ( in_array( end( $parts ), [ 'frontend.min.js', 'backend.min.js', 'common.min.js' ], true ) ) {
				$scripts[ $name ]['src'] = preg_replace( '/\.min\.js$/', '.js', $scripts[ $name ]['src'] );
			}
		}

		return $scripts;
	}
}
