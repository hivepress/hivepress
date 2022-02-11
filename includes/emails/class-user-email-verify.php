<?php
/**
 * User email verify email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Sent to users if email verification is required.
 */
class User_Email_Verify extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'Email Verification', 'hivepress' ),
				'description' => esc_html__( 'This email is sent to users when email verification is required.', 'hivepress' ),
				'recipient'   => hivepress()->translator->get_string( 'user' ),
				'tokens'      => [ 'user_name', 'email_verify_url', 'user' ],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Email Verification', 'hivepress' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Please click on the following link to verify your email address: %email_verify_url%', 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
