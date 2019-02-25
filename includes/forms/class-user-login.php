<?php
/**
 * User login form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User login form class.
 *
 * @class User_Login
 */
class User_Login extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = array_replace_recursive(
			[
				'title'   => esc_html__( 'Login User', 'hivepress' ),
				'captcha' => false,
				'fields'  => [
					'username' => [
						'label'      => esc_html__( 'Username or Email', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 254,
						'required'   => true,
						'order'      => 10,
					],

					'password' => [
						'label'    => esc_html__( 'Password', 'hivepress' ),
						'type'     => 'password',
						'required' => true,
						'order'    => 20,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
