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
 * User register email class.
 *
 * @class User_Register
 */
class User_Register extends Email {

	/**
	 * Email name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Email subject.
	 *
	 * @var string
	 */
	protected static $subject;

	/**
	 * Email body.
	 *
	 * @var string
	 */
	protected static $body;

	/**
	 * Class initializer.
	 *
	 * @param array $args Email arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Registration Complete', 'hivepress' ),
				'body'    => hp\sanitize_html( __( "Hi, %user_name%! Thank you for registering, here's your password: %user_password%", 'hivepress' ) ),
			],
			$args
		);

		parent::init( $args );
	}
}
