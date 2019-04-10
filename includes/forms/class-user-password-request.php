<?php
/**
 * User password request form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User password request form class.
 *
 * @class User_Password_Request
 */
class User_Password_Request extends Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Form title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'   => esc_html__( 'Reset Password', 'hivepress' ),
				'message' => esc_html__( 'Password reset email has been sent', 'hivepress' ),
				'action'  => hp\get_rest_url( '/users/request-password' ),

				'fields'  => [
					'username_or_email' => [
						'label'      => esc_html__( 'Username or Email', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 254,
						'required'   => true,
						'order'      => 10,
					],
				],

				'button'  => [
					'label' => esc_html__( 'Send Email', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
