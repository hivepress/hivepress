<?php
/**
 * Abstract controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract controller class.
 *
 * @class Controller
 */
abstract class Controller {
	use Traits\Mutator;

	/**
	 * Controller name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Controller routes.
	 *
	 * @var array
	 */
	protected static $routes = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Controller arguments.
	 */
	public static function init( $args = [] ) {

		// Set name.
		$args['name'] = strtolower( ( new \ReflectionClass( static::class ) )->getShortName() );

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::set_static_property( $name, $value );
		}
	}

	/**
	 * Gets controller name.
	 *
	 * @return string
	 */
	final public static function get_name() {
		return static::$name;
	}

	/**
	 * Gets controller routes.
	 *
	 * @return array
	 */
	final public static function get_routes() {
		return static::$routes;
	}

	/**
	 * Gets route query string.
	 *
	 * @param string $route_name
	 * @return string
	 */
	final public static function get_query_params( $route_name ) {
		$query_params = [];

		// Get route.
		$route = hp\hp_get_array_value( static::$routes, $route_name );

		if ( ! is_null( $route ) && isset( $route['path'] ) ) {

			preg_match_all( '/<([a-z_]+)>/i', $route['path'], $rewrite_tags );

			$rewrite_tags = array_filter( array_map( 'sanitize_title', array_map( 'current', $rewrite_tags ) ) );
		}

		return $query_params;
	}

	/**
	 * Gets route query string.
	 *
	 * @param string $route_name
	 * @return string
	 */
	final public static function get_query_string( $route_name ) {
		$query_string = '';

		// Get route.
		$route = hp\hp_get_array_value( static::$routes, $route_name );

		if ( ! is_null( $route ) && isset( $route['path'] ) ) {

			// Get rewrite tags.
			preg_match_all( '/<([a-z_]+)>/i', $route['path'], $rewrite_tags );

			$rewrite_tags = array_filter( array_map( 'sanitize_title', array_map( 'current', $rewrite_tags ) ) );

			// Get query string.
			$query_string = 'hp_controller=' . static::$name;

			if ( isset( $route['action'] ) ) {
				$query_string .= '&hp_action=' . $route['action'];
			}

			if ( ! empty( $rewrite_tags ) ) {
				$query_string .= '&' . implode(
					'&',
					array_map(
						function( $rewrite_tag ) {
							return hp\prefix( $rewrite_tag ) . '={$matches[' . $rewrite_tag . ']}';
						},
						$rewrite_tags
					)
				);
			}
		}

		return $query_string;
	}

	/**
	 * Gets route URL.
	 *
	 * @param string $route_name
	 * @return string
	 */
	final public static function get_url( $route_name ) {
		global $wp_rewrite;

		$url = '';

		// Get route.
		$route = hp\hp_get_array_value( static::$routes, $route_name );

		if ( ! is_null( $route ) && isset( $route['path'] ) ) {

			// Get URL structure.
			$url_structure = $wp_rewrite->get_page_permastruct();

			if ( false !== $url_structure ) {
				$url = $route['path'];
			} else {

			}
		}

		return home_url( $url );
	}
}
