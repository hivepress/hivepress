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
final class Form {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Set form captcha.
		add_filter( 'hivepress/v1/forms/form', [ $this, 'set_form_captcha' ] );

		// Set field options.
		add_filter( 'hivepress/v1/fields/field/args', [ $this, 'set_field_options' ] );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Sets form captcha.
	 *
	 * @param array $args Form arguments.
	 * @return array
	 */
	public function set_form_captcha( $args ) {
		if ( get_option( 'hp_recaptcha_site_key' ) && get_option( 'hp_recaptcha_secret_key' ) && in_array( $args['name'], (array) get_option( 'hp_recaptcha_forms' ), true ) ) {
			$args['captcha'] = true;
		}

		return $args;
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

			switch ( $args['options'] ) {

				// Posts.
				case 'posts':
					if ( post_type_exists( $args['post_type'] ) ) {
						$titles = null;

						$query_args = [
							'post_type'      => $args['post_type'],
							'post_status'    => 'publish',
							'posts_per_page' => -1,
							'orderby'        => 'title',
							'order'          => 'ASC',
						];

						if ( substr( $args['post_type'], 0, 3 ) === 'hp_' ) {
							$titles = hivepress()->cache->get_cache( array_merge( $query_args, [ 'fields' => 'titles' ] ), hp\unprefix( $args['post_type'] ) );
						}

						if ( is_null( $titles ) ) {
							$titles = wp_list_pluck( get_posts( $query_args ), 'post_title', 'ID' );

							if ( substr( $args['post_type'], 0, 3 ) === 'hp_' && count( $titles ) <= 1000 ) {
								hivepress()->cache->set_cache( array_merge( $query_args, [ 'fields' => 'titles' ] ), hp\unprefix( $args['post_type'] ), $titles );
							}
						}

						$options += $titles;
					}

					break;

				// Terms.
				case 'terms':
					if ( taxonomy_exists( $args['taxonomy'] ) ) {
						$names = null;

						$query_args = [
							'taxonomy'   => $args['taxonomy'],
							'fields'     => 'id=>name',
							'hide_empty' => false,
						];

						if ( substr( $args['taxonomy'], 0, 3 ) === 'hp_' ) {
							$names = hivepress()->cache->get_cache( $query_args, hp\unprefix( $args['taxonomy'] ) );
						}

						if ( is_null( $names ) ) {
							$names = get_terms( $query_args );

							if ( substr( $args['taxonomy'], 0, 3 ) === 'hp_' && count( $names ) <= 1000 ) {
								hivepress()->cache->set_cache( $query_args, hp\unprefix( $args['taxonomy'] ), $names );
							}
						}

						$options += $names;
					}

					break;

				// Forms.
				case 'forms':
					foreach ( hivepress()->get_forms() as $form_name => $form ) {
						if ( $form::get_title() ) {
							$options[ $form_name ] = $form::get_title();
						}
					}

					asort( $options );

					break;

				// Fields.
				case 'fields':
					$filters = hp\get_array_value( $args, 'field_filters', false );

					foreach ( hivepress()->get_fields() as $field_name => $field ) {
						if ( $field::get_title() && ( ( ! $filters && ! in_array( $field_name, [ 'number_range', 'date_range' ], true ) ) || ( $filters && false !== $field->get_filters() ) ) ) {
							$options[ $field_name ] = $field::get_title();
						}
					}

					asort( $options );

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

		// Get language.
		$language = hivepress()->translator->get_language();

		// Enqueue Flatpickr.
		$filepath = '/assets/js/flatpickr/l10n/' . $language . '.js';

		if ( file_exists( HP_CORE_DIR . $filepath ) ) {
			wp_enqueue_script(
				'flatpickr-' . $language,
				HP_CORE_URL . $filepath,
				[ 'flatpickr' ],
				HP_CORE_VERSION,
				true
			);
		}

		// Enqueue ReCAPTCHA.
		if ( ! is_admin() && get_option( 'hp_recaptcha_site_key' ) && get_option( 'hp_recaptcha_secret_key' ) ) {
			wp_enqueue_script(
				'recaptcha',
				'https://www.google.com/recaptcha/api.js?hl=' . $language,
				[],
				null,
				false
			);

			wp_script_add_data( 'recaptcha', 'async', true );
			wp_script_add_data( 'recaptcha', 'defer', true );
		}
	}
}
