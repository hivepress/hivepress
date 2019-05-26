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
	 * Form name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Form description.
	 *
	 * @var string
	 */
	protected static $description;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected static $model;

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'description' => esc_html__( 'Please enter a new password below.', 'hivepress' ),
				'model'       => 'user',

				'fields'      => [
					'password'           => [
						'label'    => esc_html__( 'New Password', 'hivepress' ),
						'required' => true,
						'order'    => 10,
					],

					'username'           => [
						'type' => 'hidden',
					],

					'password_reset_key' => [
						'type'     => 'hidden',
						'required' => true,
					],
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
				'action'   => hp\get_rest_url( '/users/reset-password' ),
				'redirect' => true,

				'button'   => [
					'label' => esc_html__( 'Reset Password', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
