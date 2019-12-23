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
					'label'   => esc_html__( 'Register User', 'hivepress' ),
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
