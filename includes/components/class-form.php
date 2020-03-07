<?php
/**
 * Form component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Form component class.
 *
 * @class Form
 */
final class Form extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set field options.
		add_filter( 'hivepress/v1/fields/field', [ $this, 'set_field_options' ] );

		// Manage captcha.
		if ( $this->is_captcha_enabled() ) {
			add_filter( 'hivepress/v1/forms/form/meta', [ $this, 'set_captcha' ] );
			add_filter( 'hivepress/v1/forms/form', [ $this, 'add_captcha' ], 10, 2 );
			add_filter( 'hivepress/v1/forms/form/errors', [ $this, 'validate_captcha' ], 10, 2 );
		}

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		parent::__construct( $args );
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

			// Get options.
			$option_method = 'get_' . $args['options'];
			$option_args   = hp\get_array_value( $args, 'option_args', [] );

			if ( method_exists( $this, $option_method ) ) {
				$options = call_user_func( [ $this, $option_method ], $option_args );
			}

			// Set options.
			$args['options'] = $options;
		}

		return $args;
	}

	/**
	 * Gets post options.
	 *
	 * @param array $args Post arguments.
	 * @return array
	 */
	protected function get_posts( $args ) {

		// Set default arguments.
		$args = array_merge(
			[
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			],
			$args
		);

		// Get options.
		$options = [];

		if ( post_type_exists( $args['post_type'] ) ) {

			// Get cache group.
			$cache_group = hivepress()->model->get_cache_group( 'post', $args['post_type'] );

			// Get cached options.
			$options = null;

			if ( strpos( $args['post_type'], 'hp_' ) === 0 ) {
				$options = hivepress()->cache->get_cache( array_merge( $args, [ 'fields' => 'titles' ] ), $cache_group );
			}

			if ( is_null( $options ) ) {
				$options = wp_list_pluck( get_posts( $args ), 'post_title', 'ID' );

				// Cache options.
				if ( strpos( $args['post_type'], 'hp_' ) === 0 && count( $options ) <= 1000 ) {
					hivepress()->cache->set_cache( array_merge( $args, [ 'fields' => 'titles' ] ), $cache_group, $options );
				}
			}
		}

		return $options;
	}

	/**
	 * Gets term options.
	 *
	 * @param array $args Term arguments.
	 * @return array
	 */
	protected function get_terms( $args ) {

		// Set default arguments.
		$args = array_merge(
			[
				'taxonomy'   => 'category',
				'fields'     => 'id=>name',
				'hide_empty' => false,
			],
			$args
		);

		// Set custom order.
		if ( strpos( $args['taxonomy'], 'hp_' ) === 0 ) {
			$args = array_merge(
				$args,
				[
					'orderby'    => 'meta_value_num',

					'meta_query' => [
						'relation' => 'OR',

						[
							'key'     => 'hp_sort_order',
							'type'    => 'NUMERIC',
							'compare' => 'EXISTS',
						],

						[
							'key'     => 'hp_sort_order',
							'type'    => 'NUMERIC',
							'compare' => 'NOT EXISTS',
						],
					],
				]
			);
		}

		// Get options.
		$options = [];

		if ( taxonomy_exists( $args['taxonomy'] ) ) {

			// Get cache group.
			$cache_group = hivepress()->model->get_cache_group( 'term', $args['taxonomy'] );

			// Get cached options.
			$options = null;

			if ( strpos( $args['taxonomy'], 'hp_' ) === 0 ) {
				$options = hivepress()->cache->get_cache( $args, $cache_group );
			}

			if ( is_null( $options ) ) {
				$options = get_terms( $args );

				if ( strpos( $args['taxonomy'], 'hp_' ) === 0 && count( $options ) <= 1000 ) {
					hivepress()->cache->set_cache( $args, $cache_group, $options );
				}
			}
		}

		return $options;
	}

	/**
	 * Gets form options.
	 *
	 * @param array $args Form arguments.
	 * @return array
	 */
	protected function get_forms( $args ) {
		$options = [];

		foreach ( hivepress()->get_classes( 'forms' ) as $form_name => $form ) {
			if ( $form::get_meta( 'label' ) && ! array_diff( $args, array_intersect_key( $form::get_meta(), $args ) ) ) {
				$options[ $form_name ] = $form::get_meta( 'label' );
			}
		}

		asort( $options );

		return $options;
	}

	/**
	 * Gets field options.
	 *
	 * @param array $args Field arguments.
	 * @return array
	 */
	protected function get_fields( $args ) {
		$options = [];

		foreach ( hivepress()->get_classes( 'fields' ) as $field_name => $field ) {
			if ( $field::get_meta( 'label' ) && ! array_diff( $args, array_intersect_key( $field::get_meta(), $args ) ) ) {
				$options[ $field_name ] = $field::get_meta( 'label' );
			}
		}

		asort( $options );

		return $options;
	}

	/**
	 * Checks captcha status.
	 *
	 * @return bool
	 */
	protected function is_captcha_enabled() {
		return get_option( 'hp_recaptcha_site_key' ) && get_option( 'hp_recaptcha_secret_key' );
	}

	/**
	 * Sets captcha.
	 *
	 * @param string $meta Form meta.
	 * @return array
	 */
	public function set_captcha( $meta ) {
		if ( isset( $meta['captcha'] ) && in_array( $meta['name'], (array) get_option( 'hp_recaptcha_forms' ), true ) ) {

			// Set captcha flag.
			$meta['captcha'] = true;
		}

		return $meta;
	}

	/**
	 * Adds captcha.
	 *
	 * @param array  $args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function add_captcha( $args, $form ) {
		if ( $form::get_meta( 'captcha' ) ) {

			// Add captcha field.
			$args['fields']['_captcha'] = [
				'type'      => 'captcha',
				'_separate' => true,
				'_order'    => 10000,
			];
		}

		return $args;
	}

	/**
	 * Validates captcha.
	 *
	 * @param array  $errors Form errors.
	 * @param object $form Form object.
	 * @return array
	 */
	public function validate_captcha( $errors, $form ) {
		if ( $form::get_meta( 'captcha' ) ) {

			// Get ReCAPTCHA response.
			$response = json_decode(
				wp_remote_retrieve_body(
					wp_remote_get(
						'https://www.google.com/recaptcha/api/siteverify?' . http_build_query(
							[
								'secret'   => get_option( 'hp_recaptcha_secret_key' ),
								'response' => sanitize_text_field( hp\get_array_value( $_POST, 'g-recaptcha-response' ) ),
							]
						)
					)
				),
				true
			);

			// Add form error.
			if ( ! hp\get_array_value( $response, 'success', false ) ) {
				$errors[] = esc_html__( 'Captcha is invalid.', 'hivepress' );
			}
		}

		return $errors;
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {

		// Get language.
		$language = hivepress()->translator->get_language();

		// Enqueue Flatpickr.
		$filepath = '/assets/js/flatpickr/l10n/' . $language . '.js';

		if ( file_exists( hivepress()->get_path() . $filepath ) ) {
			wp_enqueue_script(
				'flatpickr-' . $language,
				hivepress()->get_url() . $filepath,
				[ 'flatpickr' ],
				hivepress()->get_version(),
				true
			);
		}

		// Enqueue ReCAPTCHA.
		if ( ! is_admin() && $this->is_captcha_enabled() ) {
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
