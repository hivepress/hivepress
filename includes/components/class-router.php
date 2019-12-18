<?php
/**
 * Router component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Router component class.
 *
 * @class Router
 */
final class Router {

	/**
	 * The current route.
	 *
	 * @var mixed
	 */
	private $route = false;

	/**
	 * All routes.
	 *
	 * @var array
	 */
	private $routes = [];

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Register REST routes.
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		// Add rewrite rules.
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );

		// Flush rewrite rules.
		add_action( 'hivepress/v1/activate', [ $this, 'flush_rewrite_rules' ] );
		add_action( 'hivepress/v1/update', [ $this, 'flush_rewrite_rules' ] );

		if ( ! is_admin() ) {

			// Set page title.
			add_filter( 'document_title_parts', [ $this, 'set_page_title' ] );

			// Set page template.
			add_filter( 'template_include', [ $this, 'set_page_template' ], 99 );
		}
	}

	/**
	 * Registers REST routes.
	 */
	public function register_rest_routes() {
		foreach ( $this->get_routes() as $route ) {
			if ( hp\get_array_value( $route, 'rest' ) && isset( $route['path'] ) ) {
				$this->register_rest_route( $route );
			}
		}
	}

	/**
	 * Registers REST route.
	 *
	 * @param array $route Route arguments.
	 */
	private function register_rest_route( $route ) {
		if ( isset( $route['action'] ) ) {
			register_rest_route(
				'hivepress/v1',
				$route['path'],
				[
					'methods'  => hp\get_array_value( $route, 'method', 'GET' ),
					'callback' => $route['action'],
				]
			);
		}

		if ( isset( $route['routes'] ) ) {
			foreach ( $route['routes'] as $subroute ) {
				$subroute['path'] = $route['path'] . hp\get_array_value( $subroute, 'path' );

				$this->register_rest_route( $subroute );
			}
		}
	}

	/**
	 * Adds rewrite rules.
	 */
	public function add_rewrite_rules() {
		foreach ( $this->get_routes() as $name => $route ) {
			if ( ! hp\get_array_value( $route, 'rest' ) && isset( $route['path'] ) ) {
				$this->add_rewrite_rule( $name, $route );
			}
		}

		add_rewrite_tag( '%hp_route%', '([^&]+)' );
	}

	/**
	 * Adds rewrite rule.
	 *
	 * @param string $path Route path.
	 * @param array  $route Route arguments.
	 */
	private function add_rewrite_rule( $path, $route ) {
		if ( isset( $route['action'] ) || isset( $route['redirect'] ) ) {

			// Get URL params.
			$params = $this->get_url_params( $route['path'] );

			// Get query string.
			$query = implode(
				'&',
				array_merge(
					array_map(
						function( $index, $param ) {
							return hp\prefix( $param ) . '=$matches[' . ( $index + 1 ) . ']';
						},
						array_keys( $params ),
						$params
					),
					[ 'hp_route' => $path ]
				)
			);

			// Add rewrite rule.
			add_rewrite_rule( '^' . ltrim( $route['path'], '/' ) . '/?$', 'index.php?' . $query, 'top' );

			// Add rewrite tags.
			foreach ( $params as $param ) {
				add_rewrite_tag( '%' . hp\prefix( $param ) . '%', '([^&]+)' );
			}
		}

		if ( isset( $route['routes'] ) ) {
			foreach ( $route['routes'] as $subpath => $subroute ) {
				$subroute['path'] = $route['path'] . hp\get_array_value( $subroute, 'path' );

				$this->add_rewrite_rule( $path . '/' . $subname, $subroute );
			}
		}
	}

	/**
	 * Flushes rewrite rules.
	 */
	public function flush_rewrite_rules() {
		update_option( 'rewrite_rules', false );
		flush_rewrite_rules();
	}

	/**
	 * Sets page title.
	 *
	 * @param array $parts Title parts.
	 * @return string
	 */
	public function set_page_title( $parts ) {
		$route = $this->get_current_route();

		if ( $route && isset( $route['title'] ) ) {
			if ( count( $parts ) > 1 ) {
				array_shift( $parts );
			}

			array_unshift( $parts, $route['title'] );
		}

		return $parts;
	}

	/**
	 * Sets page template.
	 *
	 * @param array $template Template file.
	 * @return string
	 */
	public function set_page_template( $template ) {
		global $wp_query;

		// Get the current route.
		$route = $this->get_current_route();

		if ( $route ) {

			// Set query variables.
			if ( isset( $route['path'] ) ) {
				$wp_query->is_home = false;
				$wp_query->is_404  = false;
			}

			// todo menu redirect.
			// Redirect page.
			if ( isset( $route['redirect'] ) ) {
				$redirect = call_user_func( $route['redirect'] );

				if ( $redirect ) {
					if ( is_bool( $redirect ) ) {
						$redirect = home_url( '/' );
					}

					wp_safe_redirect( $redirect );

					exit();
				}
			}

			// Render page.
			if ( isset( $route['action'] ) ) {
				echo call_user_func( $route['action'] );

				exit();
			}
		}

		return $template;
	}

	/**
	 * Gets URL routes.
	 *
	 * @return array
	 */
	private function get_routes() {
		if ( empty( $this->routes ) ) {

			// Merge routes.
			foreach ( hivepress()->get_controllers() as $controller ) {
				$this->routes = hp\merge_arrays( $this->routes, $controller->get_routes() );
			}

			/**
			 * Filters URL routes.
			 *
			 * @filter /routes
			 * @description Filters URL routes.
			 * @param array $routes URL route arguments.
			 */
			$this->routes = apply_filters( 'hivepress/v1/routes', $this->routes );
		}

		return $this->routes;
	}

	/**
	 * Gets URL route.
	 *
	 * @param string $path Route path.
	 * @return mixed
	 */
	private function get_route( $path ) {
		$route = [ 'routes' => $this->get_routes() ];

		foreach ( explode( '/', $path ) as $name ) {
			if ( isset( $route['routes'][ $name ] ) ) {
				$route = $route['routes'][ $name ];
			} else {
				return;
			}
		}

		return $route;
	}

	/**
	 * Gets the current URL route.
	 *
	 * @return mixed
	 */
	private function get_current_route() {
		if ( false === $this->route ) {
			$this->route = null;

			if ( get_query_var( 'hp_route' ) ) {
				$this->route = $this->get_route( get_query_var( 'hp_route' ) );
			} else {
				foreach ( $this->get_routes() as $route ) {
					if ( isset( $route['match'] ) && call_user_func( $route['match'] ) ) {
						$this->route = $route;

						break;
					}
				}
			}
		}

		return $this->route;
	}

	/**
	 * Gets URL parameters.
	 *
	 * @param string $path URL path.
	 * @return array
	 */
	private function get_url_params( $path ) {

		// Get parameters.
		preg_match_all( '/<([a-z_]+)>/i', $path, $params );

		// Filter parameters.
		$params = array_filter( array_map( 'sanitize_title', array_map( 'current', $params ) ) );

		return $params;
	}

	// ----------------------------------------------------------

	/**
	 * Gets page title.
	 *
	 * @return mixed
	 */
	public function get_title() {
		return hp\get_array_value( $this->get_current_route(), 'title', get_the_title() );
	}

	public function get_url( $path, $query = [] ) {
		return '';
	}

	/**
	 * Gets route URL.
	 *
	 * @param string $path Route path.
	 * @param array  $query URL query.
	 * @return mixed
	 */
	public function get_urlasdasd( $path, $query = [] ) {
		global $wp_rewrite;

		// Get route.
		$route = $this->get_route( $path );

		if ( $route && isset( $route['path'] ) ) {
			$url = '';

			// Set URL query.
			foreach ( $this->get_url_query( $path ) as $param => $value ) {
				if ( 'route' === $param || ! isset( $query[ $param ] ) ) {
					$query[ $param ] = $value;
				}
			}

			// Get URL structure.
			$url_structure = $wp_rewrite->get_page_permastruct();

			if ( ! empty( $url_structure ) ) {
				$url = $route['path'];

				foreach ( $this->get_url_params( $path ) as $param ) {
					$url = preg_replace( '/\(\?P<' . preg_quote( $param, '/' ) . '>[^\)]+\)\??/i', $query[ $param ], $url );
				}

				$url = rtrim( str_replace( '/?', '/', $url ), '/' ) . '/';
			} else {
				$url = '?' . http_build_query( array_combine( hp\prefix( array_keys( $query ) ), $query ) );
			}

			return home_url( $url );
		}
	}
}
