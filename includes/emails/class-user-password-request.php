<?php
/**
 * User password request email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User password request email class.
 *
 * @class User_Password_Request
 */
class User_Password_Request extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'Password Reset', 'hivepress' ),
				'description' => esc_html__( 'This email is sent to users when a password reset is requested.', 'hivepress' ),
				'tokens'      => [ 'user_name', 'password_reset_url' ],
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
				'subject' => esc_html__( 'Password Reset', 'hivepress' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Please click on the following link to set a new password: %password_reset_url%', 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
