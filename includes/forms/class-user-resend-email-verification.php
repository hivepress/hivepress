<?php
/**
 * Resend user verification email form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Resend user verification email.
 */
class User_Resend_Email_Verification extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'message' => esc_html__( 'The email verification email was succesfully sent. Please check your email inbox', 'hivepress' ),
				'action'  => hivepress()->router->get_url( 'user_resend_email_verify' ),

				'fields'  => [
					'email' => [
						'label'    => esc_html__( 'Email', 'hivepress' ),
						'type'     => 'email',
						'required' => true,
						'_order'   => 10,
					],
				],

				'button'  => [
					'label' => esc_html__( 'Resend email', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
