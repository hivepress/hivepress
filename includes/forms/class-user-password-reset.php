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
	 * Form meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'meta' => [
					'model' => 'user',
				],
			],
			$args
		);

		parent::init( $args );
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
						'label'    => esc_html__( 'New Password', 'hivepress' ),
						'required' => true,
						'_order'   => 10,
					],

					'username'           => [
						'type' => 'hidden',
					],

					'password_reset_key' => [
						'type'      => 'hidden',
						'required'  => true,
						'_excluded' => true,
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
