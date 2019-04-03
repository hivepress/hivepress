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
	 * @var array
	 */
	private $route = [];

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Register API routes.
		add_action( 'rest_api_init', [ $this, 'register_api_routes' ] );

		// Manage rewrite rules.
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );
		add_action( 'hivepress/v1/activate', [ $this, 'flush_rewrite_rules' ] );

		if ( ! is_admin() ) {

			// Set page title.
			add_filter( 'document_title_parts', [ $this, 'set_page_title' ] );

			// Set page template.
			add_filter( 'template_include', [ $this, 'set_page_template' ], 99 );
		}
	}

	/**
	 * Registers API routes.
	 */
	public function register_api_routes() {
		foreach ( hivepress()->get_controllers() as $controller ) {
			foreach ( $controller::get_routes() as $route ) {
				if ( hp\get_array_value( $route, 'rest', false ) ) {
					foreach ( $route['endpoints'] as $endpoint ) {
						register_rest_route(
							'hivepress/v1',
							$route['path'] . hp\get_array_value( $endpoint, 'path' ),
							[
								'methods'  => $endpoint['methods'],
								'callback' => [ $controller, $endpoint['action'] ],
							]
						);
					}
				}
			}
		}
	}

	/**
	 * Adds rewrite rules.
	 */
	public function add_rewrite_rules() {
		foreach ( hivepress()->get_controllers() as $controller ) {
			foreach ( $controller::get_routes() as $route_name => $route ) {
				if ( ! hp\get_array_value( $route, 'rest', false ) && isset( $route['path'] ) ) {

					// Get URL query.
					$query = $controller::get_url_query( $route_name );

					// Get query string.
					$query_string = implode(
						'&',
						array_map(
							function( $param, $value ) use ( $query ) {
								if ( 'route' !== $param ) {
									$index = array_search( $param, array_keys( $query ), true );
									$value = '$matches[' . $index . ']';
								} else {
									$value = rawurlencode( $value );
								}

								return hp\prefix( $param ) . '=' . $value;
							},
							array_keys( $query ),
							$query
						)
					);

					// Add rewrite rule.
					add_rewrite_rule( '^' . ltrim( $route['path'], '/' ) . '/?$', 'index.php?' . $query_string, 'top' );

					// Add rewrite tags.
					foreach ( $controller::get_url_params( $route_name ) as $rewrite_tag ) {
						add_rewrite_tag( '%' . hp\prefix( $rewrite_tag ) . '%', '([^&]+)' );
					}
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
		if ( isset( $this->route['title'] ) ) {
			array_unshift( $parts, $this->route['title'] );
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

		foreach ( hivepress()->get_controllers() as $controller_name => $controller ) {
			foreach ( $controller::get_routes() as $route_name => $route ) {
				if ( ! hp\get_array_value( $route, 'rest', false ) && ( ( isset( $route['path'] ) && get_query_var( 'hp_route' ) === $controller_name . '/' . $route_name ) || ( isset( $route['match'] ) && call_user_func( [ $controller, $route['match'] ] ) ) ) ) {

					// Set the current route.
					$this->route = $route;

					// Set query variables.
					if ( isset( $route['path'] ) ) {
						$wp_query->is_home = false;
						$wp_query->is_404  = false;
					}

					// Redirect menu.
					$menu_redirect = null;

					foreach ( hivepress()->get_menus() as $menu ) {
						if ( $menu::is_chained() && in_array( $controller_name . '/' . $route_name, wp_list_pluck( $menu::get_items(), 'route' ), true ) ) {
							$menu_items      = $menu::get_items();
							$menu_item_names = array_keys( $menu_items );

							foreach ( $menu_items as $menu_item_name => $menu_item ) {

								// Check current item.
								$menu_item_current = ( $menu_item['route'] === $controller_name . '/' . $route_name );

								if ( $menu_item_current ) {

									// Get next item.
									$menu_item = hp\get_array_value( $menu_items, hp\get_array_value( $menu_item_names, array_search( $menu_item_name, $menu_item_names, true ) + 1 ) );
								}

								if ( ! empty( $menu_item ) ) {
									list($menu_controller_name, $menu_route_name) = explode( '/', $menu_item['route'] );

									// Get controller.
									$menu_controller = hp\get_array_value( hivepress()->get_controllers(), $menu_controller_name );

									if ( ! is_null( $menu_controller ) ) {

										// Get route.
										$menu_route = hp\get_array_value( $menu_controller::get_routes(), $menu_route_name );

										if ( ! is_null( $menu_route ) ) {
											if ( $menu_item_current ) {

												// Get menu redirect.
												$menu_redirect = $menu_controller::get_url( $menu_route_name );
											} elseif ( isset( $menu_route['redirect'] ) && call_user_func( [ $menu_controller, $menu_route['redirect'] ] ) === false ) {

												// Redirect menu item.
												wp_safe_redirect( $menu_controller::get_url( $menu_route_name ) );

												exit();
											}
										}
									}
								}

								if ( $menu_item_current ) {
									break;
								}
							}

							break;
						}
					}

					// Redirect page.
					if ( isset( $route['redirect'] ) ) {
						$redirect = call_user_func( [ $controller, $route['redirect'] ] );

						if ( ! empty( $redirect ) ) {
							if ( is_bool( $redirect ) ) {
								$redirect = home_url( '/' );

								if ( ! is_null( $menu_redirect ) ) {
									$redirect = $menu_redirect;
								}
							}

							wp_safe_redirect( $redirect );

							exit();
						}
					}

					// Render page.
					if ( isset( $route['action'] ) ) {
						echo call_user_func( [ $controller, $route['action'] ] );

						exit();
					}

					break 2;
				}
			}
		}

		return $template;
	}

	/**
	 * Gets page URL.
	 *
	 * @param string $route_path Route path.
	 * @param array  $query URL query.
	 * @return mixed
	 */
	public function get_url( $route_path, $query = [] ) {
		list($controller_name, $route_name) = explode( '/', $route_path );

		// Get controller.
		$controller = hp\get_array_value( hivepress()->get_controllers(), $controller_name );

		if ( ! is_null( $controller ) ) {
			return $controller::get_url( $route_name, $query );
		}

		return null;
	}

	/**
	 * Gets page title.
	 *
	 * @return mixed
	 */
	public function get_title() {
		return hp\get_array_value( $this->route, 'title' );
	}
}
