<?php
/**
 * User password request form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Requests a password reset.
 */
class User_Password_Request extends Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'   => esc_html__( 'Reset Password', 'hivepress' ),
				'captcha' => false,
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'description' => esc_html__( 'Please enter your username or email address, you will receive a link to create a new password via email.', 'hivepress' ),
				'message'     => esc_html__( 'Password reset email has been sent.', 'hivepress' ),
				'action'      => hivepress()->router->get_url( 'user_password_request_action' ),

				'fields'      => [
					'username_or_email' => [
						'label'      => esc_html__( 'Username or Email', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 254,
						'required'   => true,
						'_order'     => 10,

						'attributes' => [
							'autocomplete' => 'username',
						],
					],
				],

				'button'      => [
					'label' => esc_html__( 'Send Email', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
