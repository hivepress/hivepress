<?php
/**
 * Form component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Form component class.
 *
 * @class Form
 */
final class Form {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// todo.
		add_filter( 'todo123', [ $this, 'set_field_args' ] );

		// Enqueue scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	// todo.
	public function set_field_args( $args ) {
		if ( isset( $args['options'] ) && ! is_array( $args['options'] ) ) {
			$options = [];

			if ( 'select' === $args['type'] ) {
				$options = [ '' => '&mdash;' ];
			}

			switch ( $args['options'] ) {

				// Posts.
				case 'posts':
					$post_type = get_post_type_object( $args['post_type'] );

					if ( ! is_null( $post_type ) ) {
						$options += wp_list_pluck(
							get_posts(
								[
									'post_type'      => $args['post_type'],
									'post_status'    => 'publish',
									'posts_per_page' => -1,
									'orderby'        => 'title',
									'order'          => 'ASC',
								]
							),
							'post_title',
							'ID'
						);
					}

					break;

				// Terms.
				case 'terms':
					$taxonomy = get_taxonomy( $args['taxonomy'] );

					if ( false !== $taxonomy ) {
						$options += get_terms(
							[
								'taxonomy'   => $args['taxonomy'],
								'fields'     => 'id=>name',
								'hide_empty' => false,
							]
						);
					}

					break;
			}

			$args['options'] = $options;
		}

		return $args;
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {
		if ( get_option( 'hp_recaptcha_site_key' ) !== '' && get_option( 'hp_recaptcha_secret_key' ) !== '' ) {
			wp_enqueue_script(
				'recaptcha',
				'https://www.google.com/recaptcha/api.js',
				[],
				null,
				false
			);

			wp_script_add_data( 'recaptcha', 'async', true );
			wp_script_add_data( 'recaptcha', 'defer', true );
		}
	}
}
