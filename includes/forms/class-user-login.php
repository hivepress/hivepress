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
class User_Login extends Model_Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp_merge_arrays(
			$args,
			[
				'title'  => esc_html__( 'Login User', 'hivepress' ),
				'model'  => 'user',
				'action' => hp_get_rest_url( '/users/login' ),
				'fields' => [
					'username_or_email' => [
						'label'      => esc_html__( 'Username or Email', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 254,
						'required'   => true,
						'order'      => 10,
					],

					'password'          => [
						'order' => 20,
					],
				],
			]
		);

		parent::__construct( $args );
	}
}
