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
 * User password reset form class.
 *
 * @class User_Password_Reset
 */
class User_Password_Reset extends Model_Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'model'  => 'user',
				'action' => hp\get_rest_url( '/users/reset-password' ),

				'fields' => [
					'password'           => [
						'label' => esc_html__( 'New Password', 'hivepress' ),
						'order' => 10,
					],

					'username'           => [
						'type' => 'hidden',
					],

					'password_reset_key' => [
						'type'     => 'hidden',
						'required' => true,
					],
				],

				'button' => [
					'label' => esc_html__( 'Reset Password', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
