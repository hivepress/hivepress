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

		// Enqueue scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
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
