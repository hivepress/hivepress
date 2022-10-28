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
 * Sent to the user when he deleted his account.
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
				'description' => esc_html__( 'This email is sent to users after deleting account.', 'hivepress' ),
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
				'subject' => esc_html__( 'Delete account complete', 'hivepress' ),
				'body'    => hp\sanitize_html( __( "Hi, %user_name%! Your account and personal data were deleted. Thank you for using our services.", 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
