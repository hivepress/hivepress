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
 * User email verify email class.
 *
 * @class User_Email_Verify
 */
class User_Email_Verify extends Email {

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => esc_html__( 'Email Verification', 'hivepress' ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
