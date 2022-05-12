<?php
/**
 * User register email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Sent to users on account registration.
 */
class User_Register extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'User Registered', 'hivepress' ),
				'description' => esc_html__( 'This email is sent to users after registration.', 'hivepress' ),
				'recipient'   => hivepress()->translator->get_string( 'user' ),
				'tokens'      => [ 'user_name', 'user_password', 'user' ],
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
				'subject' => esc_html__( 'Registration Complete', 'hivepress' ),
				'body'    => hp\sanitize_html( __( "Hi, %user_name%! Thank you for registering, now you can access website areas that require an account.", 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
