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
 * Handles URL routing.
 */
final class Router extends Component {

	/**
	 * All routes.
	 *
	 * @var array
	 */
	protected $routes = [];

	/**
	 * The current route.
	 *
	 * @var array
	 */
	protected $route;

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Register REST routes.
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

		// Add rewrite rules.
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );

		// Set rewrite slugs.
		add_filter( 'register_post_type_args', [ $this, 'set_rewrite_slug' ], 10, 2 );
		add_filter( 'register_taxonomy_args', [ $this, 'set_rewrite_slug' ], 10, 2 );

		// Flush rewrite rules.
		add_action( 'hivepress/v1/activate', [ $this, 'flush_rewrite_rules' ] );
		add_action( 'hivepress/v1/update', [ $this, 'flush_rewrite_rules' ] );
		add_action( 'hivepress/v1/deactivate', [ $this, 'flush_rewrite_rules' ] );

		if ( ! is_admin() ) {

			// Set page title.
			add_filter( 'document_title_parts', [ $this, 'set_page_title' ] );

			// Disable page title.
			add_filter( 'rank_math/frontend/title', [ $this, 'disable_page_title' ] );

			// Set page context.
			add_filter( 'hivepress/v1/templates/page', [ $this, 'set_page_context' ] );

			// Set page template.
			add_filter( 'template_include', [ $this, 'set_page_template' ], 10000 );

			// Disable page redirect.
			add_filter( 'redirect_canonical', [ $this, 'disable_page_redirect' ] );
		}

		parent::__construct( $args );
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
			 * Filters URL routes registered by HivePress. If you customize the route URLs using this hook, don't forget to refresh permalinks afterwards.
			 *
			 * @hook hivepress/v1/routes
			 * @param {array} $routes Route configurations.
			 * @return {array} Route configurations.
			 */
			$this->routes = apply_filters( 'hivepress/v1/routes', $this->routes );
		}

		return $this->routes;
	}

	/**
	 * Gets route.
	 *
	 * @param string $name Route name.
	 * @return array
	 */
	public function get_route( $name ) {
		return hp\get_array_value( $this->get_routes(), $name );
	}

	/**
	 * Gets the current route.
	 *
	 * @return array
	 */
	public function get_current_route() {
		if ( ! isset( $this->route ) ) {
			$this->route = false;

			if ( hivepress()->request->get_param( 'route' ) ) {

				// Get route name.
				$name = hivepress()->request->get_param( 'route' );

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
	 * Gets the current route name.
	 *
	 * @return string
	 */
	public function get_current_route_name() {
		return hp\get_array_value( $this->get_current_route(), 'name' );
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
		preg_match_all( '/<([a-z_]+)>/', $this->get_url_path( $name ), $params );

		array_shift( $params );

		return hp\get_first_array_value( $params );
	}

	/**
	 * Gets route URL.
	 *
	 * @param string $name Route name.
	 * @param array  $query URL query.
	 * @param bool   $filter Remove custom query parameters?
	 * @return string
	 */
	public function get_url( $name, $query = [], $filter = false ) {
		$url = '';

		// Get route.
		$route = $this->get_route( $name );

		if ( $route ) {
			if ( isset( $route['url'] ) ) {

				// Set URL.
				$url = call_user_func( $route['url'], $query );
			} else {

				// Get URL path.
				$path = $this->get_url_path( $name );

				if ( $path ) {

					// Get URL params.
					$params = $this->get_url_params( $name );

					// Get query variables.
					$vars = array_diff_key( $query, array_flip( $params ) );

					// Set URL query.
					$query = array_merge(
						array_fill_keys( $params, null ),
						array_diff_key( $query, $vars ),
						[
							'route' => $name,
						]
					);

					// Set URL path.
					if ( get_option( 'permalink_structure' ) || hp\get_array_value( $route, 'rest' ) ) {
						foreach ( $params as $param ) {
							$path = preg_replace( '/\(\?P<' . preg_quote( $param, '/' ) . '>[^\)]+\)\??/', hp\get_array_value( $query, $param, '' ), $path );
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

					// Add query variables.
					if ( $vars && ! $filter ) {
						$url = add_query_arg( array_map( 'rawurlencode', $vars ), $url );
					}
				}
			}
		}

		return $url;
	}

	/**
	 * Gets the current URL.
	 *
	 * @return string
	 */
	public function get_current_url() {
		global $wp;

		$path = rtrim( $wp->request, '/' ) . '/';

		if ( $_GET ) {
			$path .= '?' . http_build_query( $_GET );
		}

		return home_url( $path );
	}

	/**
	 * Gets return URL.
	 *
	 * @param string $name Route name.
	 * @return string
	 */
	public function get_return_url( $name ) {
		return $this->get_url(
			$name,
			[
				'redirect' => $this->get_current_url(),
			]
		);
	}

	/**
	 * Gets admin URL.
	 *
	 * @param string $type Object type.
	 * @param int    $id Object ID.
	 * @return string
	 */
	public function get_admin_url( $type, $id ) {
		$path = '';
		$args = [];

		switch ( $type ) {
			case 'user':
				$path = $type . '-edit.php';
				$args = [
					'user_id' => $id,
				];

				break;

			case 'post':
				$path = $type . '.php';
				$args = [
					'action' => 'edit',
					'post'   => $id,
				];

				break;

			case 'comment':
				$path = $type . '.php';
				$args = [
					'action' => 'editcomment',
					'c'      => $id,
				];

				break;
		}

		return admin_url( $path . '?' . http_build_query( $args ) );
	}

	/**
	 * Gets redirect URL.
	 *
	 * @return string
	 */
	public function get_redirect_url() {
		return wp_validate_redirect( hp\get_array_value( $_GET, 'redirect' ) );
	}

	/**
	 * Gets redirect callbacks.
	 *
	 * @param array $callbacks Callback arguments.
	 * @return array
	 */
	protected function get_redirect_callbacks( $callbacks ) {

		// Normalize callbacks.
		if ( count( $callbacks ) === 2 && is_object( hp\get_first_array_value( $callbacks ) ) ) {
			$callbacks = [
				[
					'callback' => $callbacks,
					'_order'   => 5,
				],
			];
		}

		// Sort callbacks.
		$callbacks = array_filter(
			array_map(
				function( $args ) {
					return hp\get_array_value( $args, 'callback' );
				},
				hp\sort_array( $callbacks )
			)
		);

		return $callbacks;
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
						'methods'             => hp\get_array_value( $route, 'method', 'GET' ),
						'callback'            => $route['action'],
						'permission_callback' => '__return_true',
					]
				);
			}
		}
	}

	/**
	 * Adds rewrite rules.
	 */
	public function add_rewrite_rules() {

		// Set rewrite tags.
		$tags = [ 'route' ];

		foreach ( $this->get_routes() as $name => $route ) {
			if ( ! hp\get_array_value( $route, 'rest' ) && isset( $route['path'] ) && ( isset( $route['redirect'] ) || isset( $route['action'] ) ) ) {

				// Get URL path.
				$path = ltrim( $this->get_url_path( $name ), '/' );

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

				// Add rewrite rules.
				add_rewrite_rule( '^' . $path . '/?$', 'index.php?' . $query, 'top' );

				if ( hp\get_array_value( $route, 'paginated' ) ) {
					add_rewrite_rule( '^' . $path . '/page/(\d+)/?$', 'index.php?paged=$matches[' . ( count( $params ) + 1 ) . ']&' . $query, 'top' );
				}

				// Add rewrite tags.
				$tags = array_merge( $tags, $params );
			}
		}

		// Add rewrite tags.
		foreach ( array_unique( $tags ) as $tag ) {
			add_rewrite_tag( '%' . hp\prefix( $tag ) . '%', '([^&]+)' );
		}
	}

	/**
	 * Sets rewrite slug.
	 *
	 * @param array  $args Default arguments.
	 * @param string $type Post type or taxonomy.
	 * @return array
	 */
	public function set_rewrite_slug( $args, $type ) {

		// Check arguments.
		if ( strpos( $type, 'hp_' ) !== 0 || ! hp\get_array_value( $args, 'public', true ) ) {
			return $args;
		}

		// Get permalinks.
		$permalinks = (array) get_option( 'hp_permalinks', [] );

		if ( ! $permalinks ) {
			return $args;
		}

		// Set rewrite slug.
		$slug = hp\get_array_value( $permalinks, hp\unprefix( $type . '_slug' ) );

		if ( $slug ) {
			$args['rewrite']['slug'] = $slug;
		}

		return $args;
	}

	/**
	 * Flushes rewrite rules.
	 */
	public function flush_rewrite_rules() {
		delete_option( 'rewrite_rules' );
	}

	/**
	 * Sets page title.
	 *
	 * @param array $parts Title parts.
	 * @return array
	 */
	public function set_page_title( $parts ) {

		// Get the current route.
		$route = $this->get_current_route();

		if ( $route && isset( $route['title'] ) ) {

			// Remove query title.
			if ( count( $parts ) > 1 ) {
				array_shift( $parts );
			}

			// Add route title.
			array_unshift( $parts, $route['title'] );
		}

		return $parts;
	}

	/**
	 * Disables page title.
	 *
	 * @param string $title Page title.
	 * @return string
	 */
	public function disable_page_title( $title ) {
		if ( hp\get_array_value( hivepress()->router->get_current_route(), 'title' ) ) {
			return false;
		}

		return $title;
	}

	/**
	 * Sets page context.
	 *
	 * @param array $args Template arguments.
	 * @return array
	 */
	public function set_page_context( $args ) {
		$context = [];

		// Get title.
		$context['page_title'] = hp\get_array_value( $this->get_current_route(), 'title' );

		// @todo Remove theme-specific condition once fixed.
		if ( is_tax() && ( ! function_exists( 'hivetheme' ) || ! is_tax( 'hp_listing_category' ) ) ) {
			$term = get_queried_object();

			// Set title.
			$context['page_title'] = $term->name;

			// Set description.
			if ( $term->description ) {
				$context['page_description'] = apply_filters( 'the_content', $term->description );
			}
		}

		return hp\merge_arrays(
			$args,
			[
				'context' => $context,
			]
		);
	}

	/**
	 * Sets page template.
	 *
	 * @param array $template Template filepath.
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

			// Get menu redirect.
			$menu_redirect = home_url();

			foreach ( hivepress()->get_classes( 'menus' ) as $menu_class ) {
				if ( $menu_class::get_meta( 'chained' ) ) {

					// Create menu.
					$menu = hp\create_class_instance( $menu_class );

					if ( in_array( $route['name'], array_column( $menu->get_items(), 'route' ), true ) ) {

						// Get menu items.
						$menu_items      = $menu->get_items();
						$menu_item_names = array_keys( $menu_items );

						foreach ( $menu_items as $menu_item_name => $menu_item ) {
							if ( isset( $menu_item['route'] ) ) {

								// Get redirect URL.
								if ( $menu_item['route'] === $route['name'] ) {
									$next_menu_item = hp\get_array_value( $menu_items, hp\get_array_value( $menu_item_names, array_search( $menu_item_name, $menu_item_names, true ) + 1 ) );

									if ( $next_menu_item ) {
										$menu_redirect = $next_menu_item['url'];
									}

									break;
								}

								// Get menu route.
								$menu_route = $this->get_route( $menu_item['route'] );

								if ( $menu_route && isset( $menu_route['redirect'] ) ) {
									foreach ( $this->get_redirect_callbacks( $menu_route['redirect'] ) as $menu_route_redirect ) {
										if ( ! in_array( call_user_func( $menu_route_redirect ), [ null, true ], true ) ) {
											wp_safe_redirect( $menu_item['url'] );

											exit;
										}
									}
								}
							}
						}

						break;
					}
				}
			}

			// Set title.
			$title = hp\get_array_value( $route, 'title' );

			if ( is_callable( $title ) ) {
				$this->route['title'] = call_user_func( $title );
			}

			// Redirect page.
			if ( isset( $route['redirect'] ) ) {
				foreach ( $this->get_redirect_callbacks( $route['redirect'] ) as $route_redirect ) {
					$redirect = call_user_func( $route_redirect );

					if ( $redirect ) {
						if ( is_bool( $redirect ) ) {
							$redirect = $menu_redirect;
						}

						wp_safe_redirect( $redirect );

						exit;
					}
				}
			}

			// Render page.
			if ( isset( $route['action'] ) ) {
				echo call_user_func( $route['action'] );

				exit;
			}
		}

		return $template;
	}

	/**
	 * Disables page redirect.
	 *
	 * @param string $url Redirect URL.
	 * @return string
	 */
	public function disable_page_redirect( $url ) {
		foreach ( hivepress()->get_config( 'post_types' ) as $type => $args ) {
			if ( ! hp\get_array_value( $args, 'redirect_canonical', true ) && is_singular( hp\prefix( $type ) ) ) {
				$url = false;

				break;
			}
		}

		return $url;
	}
}
