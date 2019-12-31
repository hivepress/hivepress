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
	protected $route = false;

	/**
	 * All routes.
	 *
	 * @var array
	 */
	protected $routes = [];

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
	 * Gets routes.
	 *
	 * @return array
	 */
	protected function get_routes() {
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
	 * Gets route.
	 *
	 * @param string $name Route name.
	 * @return mixed
	 */
	public function get_route( $name ) {
		return hp\get_array_value( $this->get_routes(), $name );
	}

	/**
	 * Gets the current route.
	 *
	 * @return mixed
	 */
	protected function get_current_route() {
		if ( false === $this->route ) {
			$this->route = null;

			if ( get_query_var( 'hp_route' ) ) {

				// Get route name.
				$name = get_query_var( 'hp_route' );

				// Get route.
				$route = $this->get_route( $name );

				if ( $route && ! hp\get_array_value( $route, 'rest' ) ) {
					$this->route = array_merge( $route, [ 'name' => $name ] );
				}
			} else {

				// Match routes.
				foreach ( $this->get_routes() as $name => $route ) {
					if ( isset( $route['match'] ) && call_user_func( $route['match'] ) ) {
						$this->route = array_merge( $route, [ 'name' => $name ] );

						break;
					}
				}
			}
		}

		return $this->route;
	}

	/**
	 * Gets URL path.
	 *
	 * @param string $name Route name.
	 * @return string
	 */
	protected function get_url_path( $name ) {
		$path = '';

		// Get route.
		$route = $this->get_route( $name );

		// Merge paths.
		while ( $route ) {
			if ( isset( $route['path'] ) ) {
				$path = $route['path'] . $path;
			}

			if ( isset( $route['base'] ) ) {
				$route = $this->get_route( $route['base'] );
			} else {
				break;
			}
		}

		return $path;
	}

	/**
	 * Gets URL parameters.
	 *
	 * @param string $name Route name.
	 * @return array
	 */
	protected function get_url_params( $name ) {
		preg_match_all( '/<([a-z_]+)>/i', $this->get_url_path( $name ), $params );

		array_shift( $params );

		return reset( $params );
	}

	/**
	 * Gets route URL.
	 *
	 * @param string $name Route name.
	 * @param array  $query URL query.
	 * @return string
	 */
	public function get_url( $name, $query = [] ) {
		global $wp_rewrite;

		$url = '';

		// Get route.
		$route = $this->get_route( $name );

		if ( $route ) {
			if ( isset( $route['url'] ) ) {

				// Set URL.
				$url = call_user_func_array( $route['url'], [ $query ] );
			} else {

				// Get URL path.
				$path = $this->get_url_path( $name );

				if ( $path ) {

					// Get URL params.
					$params = $this->get_url_params( $name );

					// Set URL query.
					$query = array_merge(
						array_flip( $params ),
						array_intersect_key( $query, array_flip( $params ) ),
						[
							'route' => $name,
						]
					);

					// Set URL path.
					if ( $wp_rewrite->get_page_permastruct() || hp\get_array_value( $route, 'rest' ) ) {
						foreach ( $params as $param ) {
							$path = preg_replace( '/\(\?P<' . preg_quote( $param, '/' ) . '>[^\)]+\)\??/i', $query[ $param ], $path );
						}

						$path = rtrim( str_replace( '/?', '/', $path ), '/' ) . '/';
					} else {
						$path = '/?' . http_build_query( array_combine( hp\prefix( array_keys( $query ) ), $query ) );
					}

					// Set URL.
					if ( hp\get_array_value( $route, 'rest' ) ) {
						$url = get_rest_url( null, 'hivepress/v1' . $path );
					} else {
						$url = home_url( $path );
					}
				}
			}
		}

		return $url;
	}

	/**
	 * Registers REST routes.
	 */
	public function register_rest_routes() {
		foreach ( $this->get_routes() as $name => $route ) {
			if ( hp\get_array_value( $route, 'rest' ) && isset( $route['action'] ) ) {
				register_rest_route(
					'hivepress/v1',
					$this->get_url_path( $name ),
					[
						'methods'  => hp\get_array_value( $route, 'method', 'GET' ),
						'callback' => $route['action'],
					]
				);
			}
		}
	}

	/**
	 * Adds rewrite rules.
	 */
	public function add_rewrite_rules() {
		foreach ( $this->get_routes() as $name => $route ) {
			if ( ! hp\get_array_value( $route, 'rest' ) && isset( $route['path'] ) ) {

				// Get URL params.
				$params = $this->get_url_params( $name );

				// Get query string.
				$query = ltrim(
					implode(
						'&',
						array_map(
							function( $index, $param ) {
								return hp\prefix( $param ) . '=$matches[' . ( $index + 1 ) . ']';
							},
							array_keys( $params ),
							$params
						)
					) . '&hp_route=' . rawurlencode( $name ),
					'&'
				);

				// Add rewrite rule.
				add_rewrite_rule( '^' . ltrim( $this->get_url_path( $name ), '/' ) . '/?$', 'index.php?' . $query, 'top' );

				// Add rewrite tags.
				foreach ( $params as $param ) {
					add_rewrite_tag( '%' . hp\prefix( $param ) . '%', '([^&]+)' );
				}
			}
		}

		// Add route tag.
		add_rewrite_tag( '%hp_route%', '([^&]+)' );
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

		// Get the current route.
		$route = $this->get_current_route();

		if ( $route && isset( $route['title'] ) ) {
			if ( count( $parts ) > 1 ) {
				array_shift( $parts );
			}

			// Add route title.
			array_unshift( $parts, $route['title'] );
		}

		return $parts;
	}

	/**
	 * Gets page title.
	 *
	 * @return mixed
	 */
	public function get_page_title() {
		return hp\get_array_value( $this->get_current_route(), 'title', get_the_title() );
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

			// Redirect menu.
			$menu_redirect = null;

			foreach ( hivepress()->get_menus() as $menu ) {
				if ( $menu::get_meta( 'chained' ) && in_array( $route['name'], wp_list_pluck( $menu->get_items(), 'route' ), true ) ) {

					// Get menu items.
					$menu_items      = $menu->get_items();
					$menu_item_names = array_keys( $menu->get_items() );

					foreach ( $menu_items as $menu_item_name => $menu_item ) {
						if ( isset( $menu_item['route'] ) ) {

							// Get menu redirect.
							if ( $menu_item['route'] === $route['name'] ) {
								$next_menu_item = hp\get_array_value( $menu_items, hp\get_array_value( $menu_item_names, array_search( $menu_item_name, $menu_item_names, true ) + 1 ) );

								if ( $next_menu_item ) {
									$menu_redirect = $next_menu_item['url'];
								}

								break;
							}

							// Get menu route.
							$menu_route = $this->get_route( $menu_item['route'] );

							if ( $menu_route ) {
								if ( isset( $menu_route['redirect'] ) && call_user_func( $menu_route['redirect'] ) === false ) {
									wp_safe_redirect( $menu_item['url'] );

									exit();
								}
							}
						}
					}

					break;
				}
			}

			// Redirect page.
			if ( isset( $route['redirect'] ) ) {
				$redirect = call_user_func( $route['redirect'] );

				if ( $redirect ) {
					if ( is_bool( $redirect ) || ! hp\validate_redirect( $redirect ) ) {
						$redirect = $menu_redirect ? $menu_redirect : home_url( '/' );
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

	// todo.
	public function get_redirect_url( $route ) {
		// todo.
	}

	public function get_current_url() {
		global $wp;

		$query = '';

		if ( ! empty( $_GET ) ) {
			$query = '/?' . http_build_query( $_GET );
		}

		return home_url( $wp->request . $query );
	}
}
