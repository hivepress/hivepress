<?php
/**
 * User delete email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Sent to users when their account is deleted.
 */
class User_Delete extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => esc_html__( 'User Deleted', 'hivepress' ),
				'description' => esc_html__( 'This email is sent to users after their account is deleted.', 'hivepress' ),
				'recipient'   => hivepress()->translator->get_string( 'user' ),
				'tokens'      => [ 'user_name', 'user' ],
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
				'subject' => esc_html__( 'Account Deleted', 'hivepress' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Your account has been deleted, along with any associated content.', 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
