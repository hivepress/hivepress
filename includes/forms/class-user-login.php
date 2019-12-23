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
 * User login form class.
 *
 * @class User_Login
 */
class User_Login extends Model_Form {

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
					'label'   => esc_html__( 'Login User', 'hivepress' ),
					'model'   => 'user',
					'captcha' => false,
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
				'action'   => hivepress()->router->get_url( 'user_login_action' ),
				'redirect' => true,

				'fields'   => [
					'username_or_email' => [
						'label'      => esc_html__( 'Username or Email', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 254,
						'required'   => true,
						'_excluded'  => true,
						'_order'     => 10,
					],

					'password'          => [
						'min_length' => null,
						'required'   => true,
						'_order'     => 20,
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
