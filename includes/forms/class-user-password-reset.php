<?php
/**
 * User password reset form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

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
		$args = array_replace_recursive(
			$args,
			[
				'model'  => 'user',
				'action' => hp_get_rest_url( '/users/reset-password' ),
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
			]
		);

		parent::__construct( $args );
	}
}
