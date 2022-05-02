<?php
/**
 * Template component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles template rendering.
 */
final class Template extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set template title.
		add_action( 'hivepress/v1/models/post/update', [ $this, 'set_template_title' ], 10, 2 );

		if ( is_admin() ) {

			// Add admin columns.
			add_filter( 'manage_hp_template_posts_columns', [ $this, 'add_admin_columns' ] );
		} else {

			// Add theme class.
			add_filter( 'body_class', [ $this, 'add_theme_class' ] );

			// Render site header.
			add_action( 'storefront_header', [ $this, 'render_site_header' ], 31 );

			// @deprecated since version 1.3.0.
			add_action( 'hivetheme/v1/render/site_header', [ $this, 'render_site_header' ] );

			// Render site footer.
			add_action( 'wp_footer', [ $this, 'render_site_footer' ] );

			// Remove theme header.
			add_filter( 'twentynineteen_can_show_post_thumbnail', [ $this, 'remove_theme_header' ] );
			add_filter( 'ocean_display_page_header', [ $this, 'remove_theme_header' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Sets template title.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $post_type Post type.
	 */
	public function set_template_title( $post_id, $post_type ) {

		// Check post type.
		if ( 'hp_template' !== $post_type ) {
			return;
		}

		// Get post.
		$post = get_post( $post_id );

		// Get template.
		$template = '\HivePress\Templates\\' . $post->post_name;

		if ( ! class_exists( $template ) || ! $template::get_meta( 'label' ) ) {
			return;
		}

		// Update title.
		if ( $post->post_title !== $template::get_meta( 'label' ) ) {
			wp_update_post(
				[
					'ID'         => $post->ID,
					'post_title' => $template::get_meta( 'label' ),
				]
			);
		}
	}

	/**
	 * Adds admin columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function add_admin_columns( $columns ) {
		$columns['title'] = esc_html__( 'Template', 'hivepress' );

		return $columns;
	}

	/**
	 * Adds theme class.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public function add_theme_class( $classes ) {

		// Add theme class.
		$classes[] = 'hp-theme--' . hp\sanitize_slug( get_template() );

		// Add template classes.
		$route = hivepress()->router->get_current_route_name();

		if ( $route ) {
			$template = '\HivePress\Templates\\' . $route;

			if ( class_exists( $template ) ) {
				foreach ( array_slice( hp\get_class_parents( $template ), 2 ) as $class ) {
					$classes[] = 'hp-template hp-template--' . hp\sanitize_slug( hp\get_class_name( $class ) );
				}
			}
		}

		return $classes;
	}

	/**
	 * Renders site header.
	 */
	public function render_site_header() {
		echo ( new Blocks\Template( [ 'template' => 'site_header_block' ] ) )->render();
	}

	/**
	 * Renders site footer.
	 */
	public function render_site_footer() {
		echo ( new Blocks\Template( [ 'template' => 'site_footer_block' ] ) )->render();
	}

	/**
	 * Removes theme header.
	 *
	 * @param bool $display Display theme header?
	 * @return bool
	 */
	public function remove_theme_header( $display ) {
		if ( hivepress()->router->get_current_route_name() ) {
			$display = false;
		}

		return $display;
	}
}
