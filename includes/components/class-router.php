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

					// Add rewrite rule.
					add_rewrite_rule( '^' . ltrim( $route['path'], '/' ) . '/?$', 'index.php?' . $controller::get_query_string( $route_name ), 'top' );

					// Add rewrite tags.
					foreach ( $controller::get_query_params( $route_name ) as $rewrite_tag ) {
						add_rewrite_tag( '%' . hp\prefix( $rewrite_tag ) . '%', '([^&]+)' );
					}
				}
			}
		}

		// Add rewrite tags.
		add_rewrite_tag( '%hp_controller%', '([^&]+)' );
		add_rewrite_tag( '%hp_action%', '([^&]+)' );
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

		// Get controller and action.
		$controller_name = get_query_var( 'hp_controller' );
		$action_name     = get_query_var( 'hp_action' );

		foreach ( hivepress()->get_controllers() as $controller ) {
			foreach ( $controller::get_routes() as $route ) {
				if ( ! hp\get_array_value( $route, 'rest', false ) && ( ( isset( $route['path'] ) && $controller_name === $controller::get_name() && $action_name === $route['action'] ) || ( isset( $route['rule'] ) && call_user_func( [ $controller, $route['rule'] ] ) ) ) ) {

					// Set the current route.
					$this->route = $route;

					// Set query variables.
					if ( isset( $route['path'] ) ) {
						$wp_query->is_home = false;
						$wp_query->is_404  = false;
					}

					// Render page template.
					echo call_user_func( [ $controller, $route['action'] ] );

					exit();
				}
			}
		}

		return $template;
	}
}
