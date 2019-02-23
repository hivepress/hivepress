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

		// Register API routes.
		add_action( 'rest_api_init', [ $this, 'register_api_routes' ] );

		// Set field options.
		add_filter( 'hivepress/fields/field/args', [ $this, 'set_field_options' ] );

		// Enqueue scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Registers API routes.
	 */
	public function register_api_routes() {

		// Submit form.
		register_rest_route(
			'hivepress/v1',
			'/forms/(?P<name>[a-z_]+)',
			[
				'methods'  => 'POST',
				'callback' => [ $this, 'submit_form' ],
			]
		);
	}

	/**
	 * Submits form.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return mixed
	 */
	public function submit_form( $request ) {
		$response = null;

		// Get form class.
		$form_class = '\HivePress\Forms\\' . $request->get_param( 'name' );

		if ( class_exists( $form_class ) ) {

			// Create form.
			$form = new $form_class();

			// Submit form.
			if ( $form->submit() ) {
				$response = [
					'success'  => true,
					'message'  => $form->get_message(),
					'redirect' => $form->get_redirect(),
				];
			} else {
				$response = [
					'success' => false,
					'errors'  => $form->get_errors(),
				];

				//todo
				$response['values']=[];

				foreach($form->get_fields() as $field) {
					$response['values'][$field->get_name()]=$field->get_value();
				}
			}
		}

		return $response;
	}

	/**
	 * Sets field options.
	 *
	 * @param array $args Field arguments.
	 * @return array
	 */
	public function set_field_options( $args ) {
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

				// Forms.
				case 'forms':
					foreach ( hivepress()->get_forms() as $form_name => $form ) {
						if ( $form->get_title() ) {
							$options[ $form_name ] = $form->get_title();
						}
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
		if ( get_option( 'hp_recaptcha_site_key' ) && get_option( 'hp_recaptcha_secret_key' ) ) {
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
