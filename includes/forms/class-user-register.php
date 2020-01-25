<?php
/**
 * User register form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User register form class.
 *
 * @class User_Register
 */
class User_Register extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'   => esc_html__( 'Register User', 'hivepress' ),
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
				'action'   => hivepress()->router->get_url( 'user_register_action' ),
				'redirect' => true,

				'fields'   => [
					'email'    => [
						'_order' => 10,
					],

					'password' => [
						'required' => true,
						'_order'   => 20,
					],
				],

				'button'   => [
					'label' => esc_html__( 'Register', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
