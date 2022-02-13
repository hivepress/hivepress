<?php
/**
 * User password reset form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Resets user password.
 */
class User_Password_Reset extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'user',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'description' => esc_html__( 'Please enter a new password below.', 'hivepress' ),
				'action'      => hivepress()->router->get_url( 'user_password_reset_action' ),
				'redirect'    => true,

				'fields'      => [
					'password'           => [
						'label'      => esc_html__( 'New Password', 'hivepress' ),
						'required'   => true,
						'_order'     => 10,

						'attributes' => [
							'autocomplete' => 'new-password',
						],
					],

					'username'           => [
						'display_type' => 'hidden',
					],

					'password_reset_key' => [
						'type'      => 'hidden',
						'required'  => true,
						'_separate' => true,
					],
				],

				'button'      => [
					'label' => esc_html__( 'Reset Password', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
