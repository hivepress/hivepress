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
	 * Gets URL parameters.
	 *
	 * @param string $route_name Route name.
	 * @return array
	 */
	final public static function get_url_params( $route_name ) {
		$params = [];

		// Get route.
		$route = hp\get_array_value( static::$routes, $route_name );

		if ( ! is_null( $route ) && isset( $route['path'] ) ) {

			// Get parameters.
			preg_match_all( '/<([a-z_]+)>/i', $route['path'], $params );

			// Filter parameters.
			$params = array_filter( array_map( 'sanitize_title', array_map( 'current', $params ) ) );
		}

		return $params;
	}

	/**
	 * Gets URL query.
	 *
	 * @param string $route_name Route name.
	 * @return array
	 */
	final public static function get_url_query( $route_name ) {
		$query = [];

		// Get route.
		$route = hp\get_array_value( static::$routes, $route_name );

		if ( ! is_null( $route ) && isset( $route['path'] ) ) {

			// Set controller.
			$query['controller'] = static::$name;

			// Set action.
			if ( isset( $route['action'] ) ) {
				$query['action'] = $route['action'];
			}

			// Set parameters.
			foreach ( static::get_url_params( $route_name ) as $param ) {
				if ( ! in_array( $param, [ 'controller', 'action' ], true ) ) {
					$query[ $param ] = null;
				}
			}
		}

		return $query;
	}

	/**
	 * Gets route URL.
	 *
	 * @param string $route_name Route name.
	 * @param array  $query URL query.
	 * @return string
	 */
	final public static function get_url( $route_name, $query = [] ) {
		global $wp_rewrite;

		$url = '';

		// Get route.
		$route = hp\get_array_value( static::$routes, $route_name );

		if ( ! is_null( $route ) && isset( $route['path'] ) ) {

			// Set URL query.
			foreach ( static::get_url_query( $route_name ) as $param => $value ) {
				if ( in_array( $param, [ 'controller', 'action' ], true ) || ! isset( $query[ $param ] ) ) {
					$query[ $param ] = $value;
				}
			}

			// Get URL structure.
			$url_structure = $wp_rewrite->get_page_permastruct();

			if ( false !== $url_structure ) {
				$url = $route['path'];

				foreach ( static::get_url_params( $route_name ) as $param ) {
					$url = preg_replace( '/\(\?P<' . preg_quote( $param, '/' ) . '>[^\)]+\)/i', $query[ $param ], $url );
				}
			} else {
				$url = http_build_query( array_combine( hp\prefix( array_keys( $query ) ), $query ) );
			}
		}

		return home_url( $url );
	}
}
