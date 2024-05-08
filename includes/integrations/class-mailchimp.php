<?php
/**
 * Mailchimp integration.
 *
 * @package HivePress\Integrations
 */

namespace HivePress\Integrations;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * MC4WP plugin integration.
 */
final class Mailchimp extends \MC4WP_Integration {

	/**
	 * Integration name.
	 *
	 * @var string
	 */
	public $name = 'HivePress';

	/**
	 * Adds integration hooks.
	 */
	public function add_hooks() {

		// Add checkbox.
		add_filter( 'hivepress/v1/forms/user_register', [ $this, 'add_checkbox' ] );

		// Subscribe user.
		add_action( 'hivepress/v1/models/user/register', [ $this, 'subscribe_user' ], 10, 2 );
	}

	/**
	 * Checks if integration is enabled.
	 *
	 * @return bool
	 */
	public function is_installed() {
		return true;
	}

	/**
	 * Gets UI elements.
	 *
	 * @return array
	 */
	public function get_ui_elements() {
		return array_diff( parent::get_ui_elements(), [ 'implicit', 'css' ] );
	}

	/**
	 * Adds subscription checkbox.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_checkbox( $form ) {
		$form['fields']['_newsletter'] = [
			'caption'   => $this->get_label_text(),
			'type'      => 'checkbox',
			'default'   => (bool) $this->options['precheck'],
			'_separate' => true,
			'_order'    => 1001,
		];

		return $form;
	}

	/**
	 * Subscribes user.
	 *
	 * @param int   $user_id User ID.
	 * @param array $values User values.
	 */
	public function subscribe_user( $user_id, $values ) {

		// Check consent.
		if ( ! hp\get_array_value( $values, '_newsletter' ) ) {
			return;
		}

		// Check email.
		if ( ! is_email( hp\get_array_value( $values, 'email', '' ) ) ) {
			return;
		}

		// Subscribe user.
		$this->subscribe(
			[
				'EMAIL' => $values['email'],
			]
		);
	}
}
