<?php
/**
 * User request password form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User request password form class.
 *
 * @class User_Request_Password
 */
class User_Request_Password extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = array_replace_recursive(
			[
				'title'   => esc_html__( 'Reset Password', 'hivepress' ),
				'captcha' => false,
				'fields'  => [
					'username' => [
						'label'      => esc_html__( 'Username or Email', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 254,
						'required'   => true,
						'order'      => 10,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
