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
	 * Form description.
	 *
	 * @var string
	 */
	protected static $description;

	/**
	 * Form message.
	 *
	 * @var string
	 */
	protected static $message;

	/**
	 * Form action.
	 *
	 * @var string
	 */
	protected static $action;

	/**
	 * Form method.
	 *
	 * @var string
	 */
	protected static $method = 'POST';

	/**
	 * Form captcha.
	 *
	 * @var bool
	 */
	protected static $captcha = false;

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Form button.
	 *
	 * @var object
	 */
	protected static $button;

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'       => esc_html__( 'Reset Password', 'hivepress' ),
				'description' => esc_html__( 'Please enter your username or email address, you will receive a link to create a new password via email.', 'hivepress' ),
				'message'     => esc_html__( 'Password reset email has been sent', 'hivepress' ),
				'action'      => hp\get_rest_url( '/users/request-password' ),

				'fields'      => [
					'username_or_email' => [
						'label'      => esc_html__( 'Username or Email', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 254,
						'required'   => true,
						'order'      => 10,
					],
				],

				'button'      => [
					'label' => esc_html__( 'Send Email', 'hivepress' ),
				],
			],
			$args
		);

		parent::init( $args );
	}
}
