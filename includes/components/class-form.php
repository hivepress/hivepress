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
 * Handles forms.
 */
final class Form extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set field arguments.
		add_filter( 'hivepress/v1/fields/field', [ $this, 'set_field_arguments' ] );

		// Set field options.
		add_filter( 'hivepress/v1/fields/field/options', [ $this, 'set_field_options' ], 10, 2 );

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
	 * Gets field options.
	 *
	 * @param string $type Options type.
	 * @param array  $args Option arguments.
	 * @param mixed  $value Current value.
	 * @return array
	 */
	protected function get_field_options( $type, $args, $value = null ) {
		$options = [];

		// Set method.
		$method = 'get_' . $type;

		// Get options.
		if ( method_exists( $this, $method ) ) {
			$options = call_user_func_array( [ $this, $method ], [ $args, $value ] );
		} else {
			$options = hivepress()->get_config( $type );
		}

		return $options;
	}

	/**
	 * Sets field arguments.
	 *
	 * @param array $args Field arguments.
	 * @return array
	 */
	public function set_field_arguments( $args ) {
		if ( isset( $args['options'] ) && ! is_array( $args['options'] ) ) {

			// Set attributes.
			if ( 'icons' === $args['options'] ) {
				$args['attributes']['data-template'] = 'icon';
			}

			// Set options.
			if ( ! isset( $args['source'] ) ) {
				$args['options'] = $this->get_field_options( $args['options'], hp\get_array_value( $args, 'option_args', [] ) );
			}
		}

		return $args;
	}

	/**
	 * Sets field options.
	 *
	 * @param array  $options Field options.
	 * @param object $field Field object.
	 * @return array
	 */
	public function set_field_options( $options, $field ) {
		return $this->get_field_options(
			$field->get_arg( 'options' ),
			(array) $field->get_arg( 'option_args' ),
			$field->get_value()
		);
	}

	/**
	 * Gets user objects.
	 *
	 * @param array $args User arguments.
	 * @param mixed $value Current value.
	 * @return array
	 */
	protected function get_users( $args, $value ) {

		// Set default arguments.
		$args = array_merge(
			[
				'fields' => [ 'ID', 'user_login' ],
			],
			$args
		);

		// Set IDs.
		if ( $value ) {
			$args['include'] = (array) $value;
		}

		// Get users.
		$users = wp_list_pluck( get_users( $args ), 'user_login', 'ID' );

		return $users;
	}

	/**
	 * Gets post objects.
	 *
	 * @param array $args Post arguments.
	 * @param mixed $value Current value.
	 * @return array
	 */
	protected function get_posts( $args, $value ) {

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

		// Set IDs.
		if ( $value ) {
			$args['post__in'] = (array) $value;
		}

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
	 * Gets term objects.
	 *
	 * @param array $args Term arguments.
	 * @param mixed $value Current value.
	 * @return array
	 */
	protected function get_terms( $args, $value ) {

		// Set default arguments.
		$args = array_merge(
			[
				'taxonomy'   => 'category',
				'hide_empty' => false,
			],
			$args
		);

		// Set IDs.
		if ( $value ) {
			$args['include'] = (array) $value;
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
				$options = [];

				// Set custom order.
				if ( strpos( $args['taxonomy'], 'hp_' ) === 0 && ! isset( $args['orderby'] ) && get_terms(
					array_merge(
						$args,
						[
							'number'     => 1,
							'fields'     => 'ids',

							'meta_query' => [
								[
									'key'     => 'hp_sort_order',
									'value'   => 0,
									'compare' => '>',
									'type'    => 'NUMERIC',
								],
							],
						]
					)
				) ) {

					// Get terms.
					$terms = get_terms(
						array_merge(
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
						)
					);
				} else {

					// Get terms.
					$terms = get_terms( $args );
				}

				// Add options.
				foreach ( $terms as $term ) {
					$options[ $term->term_id ] = [
						'label'  => $term->name,
						'parent' => $term->parent ? $term->parent : null,
					];
				}

				// Cache options.
				if ( strpos( $args['taxonomy'], 'hp_' ) === 0 && count( $options ) <= 1000 ) {
					hivepress()->cache->set_cache( $args, $cache_group, $options );
				}
			}
		}

		return $options;
	}

	/**
	 * Gets form names.
	 *
	 * @param array $args Form arguments.
	 * @param mixed $value Current value.
	 * @return array
	 */
	protected function get_forms( $args, $value ) {
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
	 * Gets field names.
	 *
	 * @param array $args Field arguments.
	 * @param mixed $value Current value.
	 * @return array
	 */
	protected function get_fields( $args, $value ) {
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
	 * Gets MIME types.
	 *
	 * @param array $args MIME type arguments.
	 * @param mixed $value Current value.
	 * @return array
	 */
	protected function get_mime_types( $args, $value ) {
		return get_allowed_mime_types();
	}

	/**
	 * Gets template names.
	 *
	 * @param array $args Template arguments.
	 * @param mixed $value Current value.
	 * @return array
	 */
	protected function get_templates( $args, $value ) {
		$options = [];

		foreach ( hivepress()->get_classes( 'templates' ) as $template_name => $template ) {
			if ( $template::get_meta( 'label' ) ) {
				$options[ $template_name ] = $template::get_meta( 'label' );
			}
		}

		asort( $options );

		return $options;
	}

	/**
	 * Gets days of the week.
	 *
	 * @param array $args Day arguments.
	 * @param mixed $value Current value.
	 * @return array
	 */
	protected function get_days( $args, $value ) {
		return array_map(
			function( $day ) {
				return date_i18n( 'D', strtotime( $day ) );
			},
			[
				1 => 'monday',
				2 => 'tuesday',
				3 => 'wednesday',
				4 => 'thursday',
				5 => 'friday',
				6 => 'saturday',
				0 => 'sunday',
			]
		);
	}

	/**
	 * Gets time zones.
	 *
	 * @return array
	 */
	protected function get_timezones() {

		// Load textdomain.
		if ( ! is_textdomain_loaded( 'continents-cities' ) ) {
			load_textdomain( 'continents-cities', WP_LANG_DIR . '/continents-cities-' . get_locale() . '.mo' );
		}

		// Get timezones.
		$timezones = [];

		foreach ( timezone_identifiers_list() as $name ) {
			$labels = [];

			foreach ( explode( '/', str_replace( '_', ' ', $name ) ) as $label ) {
				$labels[] = translate( $label, 'continents-cities' );
			}

			if ( count( $labels ) > 1 ) {
				array_shift( $labels );
			}

			$timezones[ $name ] = implode( ' - ', $labels );
		}

		// Sort timezones.
		asort( $timezones );

		return $timezones;
	}

	/**
	 * Gets email names.
	 *
	 * @param array $args Email arguments.
	 * @param mixed $value Current value.
	 * @return array
	 */
	protected function get_emails( $args, $value ) {
		$options = [];

		foreach ( hivepress()->get_classes( 'emails' ) as $email_name => $email ) {
			if ( $email::get_meta( 'label' ) ) {
				$options[ $email_name ] = $email::get_meta( 'label' );
			}
		}

		asort( $options );

		return $options;
	}

	/**
	 * Checks if captcha is enabled.
	 *
	 * @return bool
	 */
	protected function is_captcha_enabled() {
		return get_option( 'hp_recaptcha_site_key' ) && get_option( 'hp_recaptcha_secret_key' );
	}

	/**
	 * Sets captcha flag.
	 *
	 * @param string $meta Class meta.
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
	 * Adds captcha field.
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
		$filepath = '/node_modules/flatpickr/dist/l10n/' . ( 'el' === $language ? 'gr' : $language ) . '.js';

		if ( file_exists( hivepress()->get_path() . $filepath ) ) {
			wp_enqueue_script(
				'flatpickr-' . $language,
				hivepress()->get_url() . $filepath,
				[ 'flatpickr' ],
				hivepress()->get_version(),
				true
			);
		}

		if ( ! is_admin() ) {

			// Enqueue Select2.
			$filepath = '/node_modules/select2/dist/js/i18n/' . $language . '.js';

			if ( file_exists( hivepress()->get_path() . $filepath ) ) {
				wp_enqueue_script(
					'select2-' . $language,
					hivepress()->get_url() . $filepath,
					[ 'select2-full' ],
					hivepress()->get_version(),
					true
				);
			}

			// Enqueue ReCAPTCHA.
			if ( $this->is_captcha_enabled() ) {
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
}
