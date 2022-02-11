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
 * Implements integration with developer tools.
 */
final class Debug extends Component {

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

		// Alter style tag.
		add_filter( 'style_loader_tag', [ $this, 'alter_style_tag' ], 10, 3 );

		// Alter scripts.
		add_filter( 'hivepress/v1/scripts', [ $this, 'alter_scripts' ] );
		add_filter( 'hivetheme/v1/scripts', [ $this, 'alter_scripts' ] );

		parent::__construct( $args );
	}

	/**
	 * Checks if debug is enabled.
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

			if ( in_array( hp\get_last_array_value( $parts ), [ 'style.css', 'frontend.min.css', 'backend.min.css', 'common.min.css' ], true ) ) {
				$styles[ $name ]['src'] = preg_replace( '/(\.min)?\.css$/', '.less', $style['src'] );
			}
		}

		return $styles;
	}

	/**
	 * Alters style tag.
	 *
	 * @param string $tag Style HTML.
	 * @param string $handle Style handle.
	 * @param string $src Style URL.
	 * @return string
	 */
	public function alter_style_tag( $tag, $handle, $src ) {
		if ( strpos( $src, '.less?' ) ) {
			$tag = str_replace( "rel='stylesheet'", "rel='stylesheet/less'", $tag );
		}

		return $tag;
	}

	/**
	 * Alters scripts.
	 *
	 * @param array $scripts Scripts.
	 * @return array
	 */
	public function alter_scripts( $scripts ) {

		// Alter scripts.
		foreach ( $scripts as $name => $script ) {
			$parts = explode( '/', hp\get_array_value( $script, 'src' ) );

			if ( in_array( hp\get_last_array_value( $parts ), [ 'frontend.min.js', 'backend.min.js', 'common.min.js' ], true ) ) {
				$scripts[ $name ]['src'] = preg_replace( '/\.min\.js$/', '.js', $scripts[ $name ]['src'] );
			}
		}

		// Enqueue LESS.
		$scripts['less'] = [
			'handle'    => 'less',
			'src'       => '//cdnjs.cloudflare.com/ajax/libs/less.js/3.9.0/less.min.js',
			'version'   => false,
			'in_footer' => false,
			'scope'     => [ 'frontend', 'backend' ],
		];

		return $scripts;
	}
}
