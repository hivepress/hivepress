<?php
/**
 * User resend email verify page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Resend email verification page.
 */
class User_Email_Verify_Resend_Page extends Page_Narrow {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => esc_html__( 'Email Verification', 'hivepress' ),
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_content' => [
						'blocks' => [
							'user_email_verify_message' => [
								'type'   => 'part',
								'path'   => 'user/register/user-email-verify-message',
								'_label' => hivepress()->translator->get_string( 'message' ),
								'_order' => 10,
							],

							'user_resend_email_verification_form' => [
								'type'   => 'form',
								'form'   => 'user_resend_email_verification',
								'_order' => 20,
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
