<?php
/**
 * User login form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Logs user in.
 */
class User_Login extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'   => esc_html__( 'Login User', 'hivepress' ),
				'model'   => 'user',
				'captcha' => false,
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
				'action'   => hivepress()->router->get_url( 'user_login_action' ),
				'redirect' => true,

				'fields'   => [
					'username_or_email' => [
						'label'      => esc_html__( 'Username or Email', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 254,
						'required'   => true,
						'_separate'  => true,
						'_order'     => 10,

						'attributes' => [
							'autocomplete' => 'username',
						],
					],

					'password'          => [
						'min_length' => null,
						'required'   => true,
						'_order'     => 20,

						'attributes' => [
							'autocomplete' => 'current-password',
						],
					],
				],

				'button'   => [
					'label' => esc_html__( 'Sign In', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
