<?php
/**
 * User reset password form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User reset password form class.
 *
 * @class User_Reset_Password
 */
class User_Reset_Password extends Form {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->fields = [
			'password' => [
				'label'      => esc_html__( 'New Password', 'hivepress' ),
				'type'       => 'password',
				'min_length' => 6,
				'required'   => true,
				'order'      => 10,
			],

			'username' => [
				'type'     => 'hidden',
				'required' => true,
				'default'  => sanitize_user( hp_get_array_value( $_GET, 'username' ) ),
			],

			'key'      => [
				'type'     => 'hidden',
				'required' => true,
				'default'  => sanitize_text_field( hp_get_array_value( $_GET, 'key' ) ),
			],
		];

		parent::__construct();
	}

	/**
	 * Submits form.
	 */
	public function submit() {
		parent::submit();

		// Get user.
		$user = check_password_reset_key( $values['key'], $values['username'] );

		if ( ! is_wp_error( $user ) ) {

			// Reset password.
			reset_password( $user, $values['password'] );

			// Authenticate user.
			wp_signon(
				[
					'user_login'    => $values['username'],
					'user_password' => $values['password'],
					'remember'      => true,
				],
				is_ssl()
			);

			// Send email.
			wp_password_change_notification( $user );
		} else {
			$this->errors[] = esc_html__( 'Password reset link is expired or invalid.', 'hivepress' );
		}
	}
}
