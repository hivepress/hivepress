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
class User_Resend_Email_Verify_Page extends Page_Narrow {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => esc_html__( 'Resend Verification Email', 'hivepress' ),
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
							'user_resend_email_verify_form' => [
								'type'   => 'form',
								'form'   => 'user_resend_email_verify',
								'_order' => 10,
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
