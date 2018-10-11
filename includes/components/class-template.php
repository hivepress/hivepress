<?php
namespace HivePress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages front-end.
 *
 * @class Template
 */
class Template extends Component {

	/**
	 * Array of pages.
	 *
	 * @var array
	 */
	private $pages = [];

	/**
	 * The current page.
	 *
	 * @var array
	 */
	private $page = [];

	/**
	 * Array of templates.
	 *
	 * @var array
	 */
	private $templates = [];

	/**
	 * Array of template data.
	 *
	 * @var array
	 */
	private $context = [];

	/**
	 * Array of post types.
	 *
	 * @var array
	 */
	private $post_types = [];

	/**
	 * Array of taxonomies.
	 *
	 * @var array
	 */
	private $taxonomies = [];

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings );

		// Manage pages.
		add_action( 'hivepress/component/init_pages', [ $this, 'init_pages' ], 10, 2 );
		add_action( 'init', [ $this, 'add_pages' ] );
		add_action( 'hivepress/core/activate_plugin', [ $this, 'update_pages' ] );

		// Initialize templates.
		add_action( 'hivepress/component/init_templates', [ $this, 'init_templates' ] );

		if ( ! is_admin() ) {

			// Initialize post types and taxonomies.
			add_action( 'hivepress/component/init_post_types', [ $this, 'init_post_types' ] );
			add_action( 'hivepress/component/init_taxonomies', [ $this, 'init_taxonomies' ] );

			// Initialize the current page.
			add_action( 'parse_query', [ $this, 'init_page' ] );

			// Set page title.
			add_filter( 'document_title_parts', [ $this, 'set_title' ] );

			// Manage templates.
			add_filter( 'template_include', [ $this, 'set_template' ] );
			add_action( 'get_header', [ $this, 'register_parts' ] );

			// Add theme class.
			add_filter( 'body_class', [ $this, 'add_theme_class' ] );

			// Render page template.
			add_action( 'storefront_header', [ $this, 'render_page_header' ], 31 );
			add_action( 'hivepress/template/render_area/page__content', [ $this, 'render_page_start' ], 1 );
			add_action( 'hivepress/template/render_area/page__content', [ $this, 'render_page_end' ], 1000 );
			add_action( 'wp_footer', [ $this, 'render_page_footer' ] );
		}
	}

	/**
	 * Initializes post types.
	 *
	 * @param array $post_types
	 */
	public function init_post_types( $post_types ) {
		$this->post_types = array_merge( $this->post_types, array_keys( $post_types ) );
	}

	/**
	 * Initializes taxonomies.
	 *
	 * @param array $taxonomies
	 */
	public function init_taxonomies( $taxonomies ) {
		$this->taxonomies = array_merge( $this->taxonomies, array_keys( $taxonomies ) );
	}

	/**
	 * Initializes pages.
	 *
	 * @param array  $pages
	 * @param string $component_name
	 */
	public function init_pages( $pages, $component_name ) {
		$this->pages = array_merge(
			$this->pages,
			array_combine(
				array_map(
					function( $page_name ) use ( $component_name ) {
						return $component_name . '__' . $page_name;
					},
					array_keys( $pages )
				),
				$pages
			)
		);
	}

	/**
	 * Adds pages.
	 */
	public function add_pages() {
		foreach ( $this->pages as $page ) {

			// Add rewrite rule.
			add_rewrite_rule( $page['regex'], $page['redirect'], hp_get_array_value( $page, 'after', 'top' ) );

			// Parse rewrite tags.
			$rewrite_query = hp_get_array_value( array_reverse( explode( '?', $page['redirect'] ) ), 0, '' );

			parse_str( $rewrite_query, $rewrite_tags );

			// Add rewrite tags.
			foreach ( array_keys( $rewrite_tags ) as $rewrite_tag ) {
				add_rewrite_tag( '%' . $rewrite_tag . '%', '([^&]+)' );
			}
		}
	}

	/**
	 * Updates pages.
	 */
	public function update_pages() {
		update_option( 'rewrite_rules', false );
		flush_rewrite_rules();
	}

	/**
	 * Initializes the current page.
	 *
	 * @param WP_Query $query
	 */
	public function init_page( $query ) {
		if ( $query->is_main_query() ) {
			foreach ( $this->pages as $page_id => $page ) {
				$rewrite_query = hp_get_array_value( array_reverse( explode( '?', $page['redirect'] ) ), 0, '' );

				// Parse query.
				parse_str( $rewrite_query, $rewrite_tags );

				// Get page.
				foreach ( array_keys( $rewrite_tags ) as $rewrite_tag ) {
					if ( get_query_var( $rewrite_tag ) ) {
						$query->is_home = false;
						$query->is_404  = false;

						// Filter page title.
						$page['title'] = apply_filters( "hivepress/template/page_title/{$page_id}", hp_get_array_value( $page, 'title', '' ) );

						$this->page = array_merge( $page, [ 'id' => $page_id ] );

						break 2;
					}
				}
			}
		}
	}

	/**
	 * Sets page title.
	 *
	 * @param array $parts
	 * @return string
	 */
	public function set_title( $parts ) {
		if ( isset( $this->page['title'] ) ) {
			array_unshift( $parts, $this->page['title'] );
		}

		return $parts;
	}

	/**
	 * Gets page title.
	 *
	 * @return string
	 */
	private function get_title() {
		$title = '';

		if ( is_tax() ) {
			$title = single_term_title( '', false );
		} elseif ( isset( $_GET['category'] ) ) {
			$term = get_term( absint( $_GET['category'] ) );

			if ( ! is_null( $term ) && ! is_wp_error( $term ) ) {
				$title = $term->name;
			}
		} elseif ( isset( $this->page['title'] ) ) {
			$title = $this->page['title'];
		}

		return $title;
	}

	/**
	 * Renders page title.
	 *
	 * @param array $args
	 * @return string
	 */
	public function render_title( $args ) {
		$output = '';

		// Set default arguments.
		$args = hp_merge_arrays(
			[
				'before' => '',
				'after'  => '',
			],
			$args
		);

		// Get title.
		$title = $this->get_title();

		// Render title.
		if ( '' !== $title ) {
			$output = $args['before'] . esc_html( $title ) . $args['after'];
		}

		return $output;
	}

	/**
	 * Gets page URL.
	 *
	 * @param string $page_id
	 * @param array  $query
	 * @return string
	 */
	public function get_url( $page_id, $query = [ 1 ] ) {
		global $wp_rewrite;

		$url = '';

		// Get URL structure.
		$url_structure = $wp_rewrite->get_page_permastruct();

		// Get page.
		$page = hp_get_array_value( $this->pages, $page_id );

		// Get page URL.
		if ( ! is_null( $page ) ) {
			if ( ! empty( $url_structure ) ) {

				// Set query.
				preg_match_all( '/\([^\)]+\)/', $page['regex'], $matches );

				foreach ( $matches as $match_index => $match ) {
					$page['regex'] = str_replace( $match, hp_get_array_value( $query, $match_index, '' ), $page['regex'] );
				}

				// Get URL.
				$url = home_url( str_replace( '%pagename%', trim( $page['regex'], '\+*?[^]$(){}=!<>|:-' ), $url_structure ) );
			} else {

				// Set query.
				preg_match_all( '/\$matches\[[0-9]+\]/', $page['redirect'], $matches );

				foreach ( $matches as $match_index => $match ) {
					$page['redirect'] = str_replace( $match, hp_get_array_value( $query, $match_index, '' ), $page['redirect'] );
				}

				// Get URL.
				$url = home_url( $page['redirect'] );
			}
		}

		return $url;
	}

	/**
	 * Initializes templates.
	 *
	 * @param array $templates
	 */
	public function init_templates( $templates ) {
		$this->templates = hp_merge_arrays( $this->templates, $templates );
	}

	/**
	 * Sets page template.
	 *
	 * @param string $template_path
	 * @return string
	 */
	public function set_template( $template_path ) {
		$template_id = false;

		// Get post types and taxonomies.
		$post_types = hp_prefix( $this->post_types );
		$taxonomies = hp_prefix( $this->taxonomies );

		// Get current post type.
		$post_type = hp_get_array_value( $_GET, 'post_type', get_post_type() );

		if ( is_page() ) {
			$page_id = get_queried_object_id();

			foreach ( $post_types as $current_post_type ) {
				$current_page_id = absint( get_option( 'hp_page_' . hp_unprefix( $current_post_type ) . 's' ) );

				if ( $current_page_id === $page_id ) {
					$post_type = $current_post_type;

					break;
				}
			}
		}

		if ( in_array( $post_type, $post_types ) ) {

			// Check archive pages.
			if ( is_post_type_archive( $post_types ) ) {
				$template_id = hp_unprefix( $post_type ) . '_archive';
			} elseif ( is_page() ) {
				if ( get_option( 'hp_page_' . hp_unprefix( $post_type ) . 's_display_subcategories' ) ) {
					$template_id = 'category_archive';
				} else {
					$template_id = hp_unprefix( $post_type ) . '_archive';
				}
			} elseif ( is_tax( $taxonomies ) ) {
				if ( get_term_meta( get_queried_object_id(), 'hp_display_subcategories', true ) ) {
					$template_id = 'category_archive';
				} else {
					$template_id = hp_unprefix( $post_type ) . '_archive';
				}

				// Check singlular pages.
			} elseif ( is_singular() ) {
				$template_id = 'single_' . hp_unprefix( $post_type );
			}

			// Check static pages.
		} elseif ( ! empty( $this->page ) ) {
			if ( ! isset( $this->page['capability'] ) || ( ( 'login' === $this->page['capability'] && ! is_user_logged_in() ) || current_user_can( $this->page['capability'] ) ) ) {
				if ( isset( $this->page['template'] ) ) {
					$template = hp_get_array_value( $this->templates, $this->page['template'] );

					if ( ! is_null( $template ) ) {
						if ( isset( $template['parent'] ) ) {
							$template_parent = hp_get_array_value( $this->templates, $template['parent'] );

							if ( ! is_null( $template_parent ) ) {
								$this->templates[ $this->page['template'] ]['path'] = $template_parent['path'];
								$this->templates[ $template['parent'] ]['areas']    = hp_merge_arrays( $template_parent['areas'], $template['areas'] );
							}
						}

						$template_id = $this->page['template'];
					}
				}

				// Get page ID.
				$page_id = $this->page['id'];

				// Fires when page template is set.
				do_action( "hivepress/template/redirect_page/{$page_id}" );
			} else {

				// Filter redirect URL.
				$redirect_url = apply_filters( 'hivepress/template/redirect_url', home_url() );

				// Redirect user.
				hp_redirect( $redirect_url );
			}
		}

		// Set template path.
		if ( false !== $template_id ) {
			$this->templates['page']['areas']['content'][ $template_id ] = [
				'template' => $template_id,
				'order'    => 10,
			];

			echo $this->render_template( 'page' );

			exit;
		}

		return $template_path;
	}

	/**
	 * Gets template path.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get_template_path( $name ) {
		$template_path = locate_template( 'hivepress/' . $name . '.php' );

		if ( empty( $template_path ) ) {
			foreach ( hivepress()->get_plugin_paths() as $plugin_path ) {
				if ( file_exists( $plugin_path . '/templates/' . $name . '.php' ) ) {
					$template_path = $plugin_path . '/templates/' . $name . '.php';

					break;
				}
			}
		}

		return $template_path;
	}

	/**
	 * Registers template parts.
	 */
	public function register_parts() {
		foreach ( $this->templates as $template_id => $template ) {
			if ( isset( $template['areas'] ) ) {
				foreach ( $template['areas'] as $area_id => $parts ) {
					foreach ( $parts as $part_id => $part ) {
						add_action( 'hivepress/template/render_area/' . $template_id . '__' . $area_id, [ $this, 'render_' . $template_id . '__' . $area_id . '__' . $part_id ], hp_get_array_value( $part, 'order', 0 ) );
					}
				}
			}
		}
	}

	/**
	 * Routes component functions.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __call( $name, $args ) {
		parent::__call( $name, $args );

		// Render template parts.
		if ( strpos( $name, 'render_' ) === 0 ) {

			// Get template part IDs.
			list($template_id, $area_id, $part_id) = explode( '__', str_replace( 'render_', '', $name ) );

			if ( isset( $this->templates[ $template_id ]['areas'][ $area_id ][ $part_id ] ) ) {

				// Get template part.
				$part = $this->templates[ $template_id ]['areas'][ $area_id ][ $part_id ];

				// Render template part.
				if ( isset( $part['path'] ) ) {
					echo $this->render_part( $part['path'], hp_get_array_value( $this->context, $template_id, [] ) );
				} elseif ( isset( $part['template'] ) ) {
					echo $this->render_template( $part['template'] );
				}
			}
		}
	}

	/**
	 * Renders template.
	 *
	 * @param string $template_id
	 * @param array  $context
	 * @return string
	 */
	public function render_template( $template_id, $context = [] ) {
		$output = '';

		if ( isset( $this->templates[ $template_id ] ) ) {

			// Get template path.
			$path = $this->get_template_path( $this->templates[ $template_id ]['path'] );

			if ( ! empty( $path ) ) {

				// Set template context.
				$context_id = hp_get_array_value( $this->templates[ $template_id ], 'parent', $template_id );

				$this->context[ $context_id ] = apply_filters( "hivepress/template/template_context/{$template_id}", $context );

				extract( $this->context[ $context_id ], EXTR_SKIP );

				// Render template HTML.
				ob_start();

				include $path;
				$output = ob_get_contents();

				ob_end_clean();
			}
		}

		return $output;
	}

	/**
	 * Renders template area.
	 *
	 * @param string $area_id
	 * @return string
	 */
	public function render_area( $area_id ) {
		ob_start();

		do_action( "hivepress/template/render_area/{$area_id}" );
		$output = ob_get_contents();

		ob_end_clean();

		return $output;
	}

	/**
	 * Renders template part.
	 *
	 * @param string $name
	 * @param array  $context
	 * @return string
	 */
	public function render_part( $name, $context = [] ) {
		$output = '';

		// Get template path.
		$path = $this->get_template_path( $name );

		if ( ! empty( $path ) ) {

			// Set template context.
			extract( $context, EXTR_SKIP );

			// Render template part HTML.
			ob_start();

			include $path;
			$output = ob_get_contents();

			ob_end_clean();
		}

		return $output;
	}

	/**
	 * Gets menu items.
	 *
	 * @param string $name
	 */
	public function get_menu( $name ) {

		// Get pages.
		$pages = array_filter(
			$this->pages,
			function( $page ) use ( $name ) {
				return hp_get_array_value( $page, 'menu' ) === $name;
			}
		);

		// Set menu items.
		$items = [];

		foreach ( $pages as $page_id => $page ) {
			$items[ $page_id ] = [
				'name'   => $page['title'],
				'url'    => $this->get_url( $page_id ),
				'order'  => hp_get_array_value( $page, 'order', 0 ),
				'parent' => hp_get_array_value( $page, 'parent' ),
			];
		}

		// Sort menu items.
		$items = hp_sort_array( $items );

		return $items;
	}

	/**
	 * Renders menu.
	 *
	 * @param string $name
	 */
	public function render_menu( $name ) {
		return $this->render_submenu( $this->get_menu( $name ), hp_get_array_value( $this->page, 'id' ) );
	}

	/**
	 * Renders submenu.
	 *
	 * @param array  $all_items
	 * @param string $selected_item
	 * @param string $current_item
	 * @return string
	 */
	public function render_submenu( $all_items, $selected_item, $current_item = false ) {
		$output = '';

		// Get menu items.
		$items = array_filter(
			$all_items,
			function( $item ) use ( $current_item ) {
				$parent_item = hp_get_array_value( $item, 'parent' );

				return ( false === $current_item && is_null( $parent_item ) ) || ( false !== $current_item && $parent_item === $current_item );
			}
		);

		// Render menu HTML.
		if ( ! empty( $items ) ) {
			if ( false === $current_item ) {
				$output .= '<ul>';
			} else {
				$output .= '<ul class="children">';
			}

			foreach ( $items as $item_id => $item ) {
				$output .= '<li';

				// Check selected item.
				if ( $selected_item === $item_id ) {
					$output .= ' class="current-menu-item current-cat"';
				}

				$output .= '><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['name'] ) . '</a>';

				// Render submenu HTML.
				$output .= $this->render_submenu( $all_items, $selected_item, $item_id );

				$output .= '</li>';
			}

			$output .= '</ul>';
		}

		return $output;
	}

	/**
	 * Renders page start.
	 */
	public function render_page_start() {
		$output = '';

		// Get current theme.
		$template = get_template();

		// Open page wrapper.
		switch ( $template ) {
			case 'twentyten':
				$output .= '<div id="container"><div id="content" role="main">';

				break;

			case 'twentyeleven':
				$output .= '<div id="primary"><div id="content" role="main">';

				break;

			case 'twentytwelve':
				$output .= '<div id="primary" class="site-content"><div id="content" role="main">';

				break;

			case 'twentythirteen':
				$output .= '<div id="primary" class="site-content"><div id="content" role="main" class="entry-content">';

				break;

			case 'twentyfourteen':
				$output .= '<div id="primary" class="content-area"><div id="content" role="main" class="site-content">';

				break;

			case 'twentyfifteen':
				$output .= '<div id="primary" role="main" class="content-area"><div id="main" class="site-main">';

				break;

			case 'twentyseventeen':
				$output .= '<div class="wrap"><div id="primary" class="content-area"><main id="main" class="site-main" role="main">';

				break;

			default:
				$output .= '<div id="primary" class="content-area"><main id="main" class="site-main" role="main">';

				break;
		}

		echo $output;
	}

	/**
	 * Renders page end.
	 */
	public function render_page_end() {
		$output = '';

		// Get current theme.
		$template = get_template();

		// Close page wrapper.
		switch ( $template ) {
			case 'twentyten':
			case 'twentyeleven':
			case 'twentytwelve':
			case 'twentythirteen':
			case 'twentyfourteen':
			case 'twentyfifteen':
				$output .= '</div></div>';

				break;

			case 'twentyseventeen':
				$output .= '</main></div></div>';

				break;

			default:
				$output .= '</main></div>';

				break;
		}

		echo $output;
	}

	/**
	 * Adds theme class.
	 *
	 * @param array $classes
	 * @return array
	 */
	public function add_theme_class( $classes ) {
		return array_merge( $classes, [ 'hp-theme--' . get_template() ] );
	}

	/**
	 * Renders page header.
	 */
	public function render_page_header() {
		echo $this->render_area( 'page__header' );
	}

	/**
	 * Renders page footer.
	 */
	public function render_page_footer() {
		echo $this->render_area( 'page__footer' );
	}

	/**
	 * Renders result count.
	 */
	public function render_result_count() {
		global $wp_query;

		$output = '';

		if ( $wp_query->found_posts > 0 ) {

			// Get first result.
			$first_result = 1;

			if ( hp_get_current_page() > 1 ) {
				$first_result = $wp_query->query_vars['posts_per_page'] * ( hp_get_current_page() - 1 ) + 1;
			}

			// Get last result.
			$last_result = $first_result + $wp_query->post_count - 1;

			$output .= sprintf( esc_html__( 'Showing %1$s-%2$s of %3$s results', 'hivepress' ), $first_result, $last_result, $wp_query->found_posts );
		}

		return $output;
	}
}
