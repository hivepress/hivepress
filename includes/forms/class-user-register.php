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
	 * Form title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected static $model;

	/**
	 * Form action.
	 *
	 * @var string
	 */
	protected static $action;

	/**
	 * Form captcha.
	 *
	 * @var bool
	 */
	protected static $captcha = false;

	/**
	 * Form redirect.
	 *
	 * @var mixed
	 */
	protected static $redirect = false;

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
				'title'    => esc_html__( 'Register User', 'hivepress' ),
				'model'    => 'user',
				'action'   => hp\get_rest_url( '/users' ),
				'redirect' => true,

				'fields'   => [
					'email'    => [
						'order' => 10,
					],

					'password' => [
						'required' => true,
						'order'    => 20,
					],
				],

				'button'   => [
					'label' => esc_html__( 'Register', 'hivepress' ),
				],
			],
			$args
		);

		parent::init( $args );
	}
}
