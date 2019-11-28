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

		// Set fields.
		$fields = [
			'email'    => [
				'order' => 10,
			],

			'password' => [
				'required' => true,
				'order'    => 20,
			],
		];

		// Add terms checkbox.
		$page_id = hp\get_post_id(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post__in'    => [ absint( get_option( 'hp_page_user_registration_terms' ) ) ],
			]
		);

		if ( 0 !== $page_id ) {
			$fields['terms'] = [
				'caption'  => sprintf( hp\sanitize_html( __( 'I agree to the <a href="%s" target="_blank">terms and conditions</a>', 'hivepress' ) ), esc_url( get_permalink( $page_id ) ) ),
				'type'     => 'checkbox',
				'required' => true,
				'order'    => 1000,
			];
		}

		// Set arguments.
		$args = hp\merge_arrays(
			[
				'title'    => esc_html__( 'Register User', 'hivepress' ),
				'model'    => 'user',
				'action'   => hp\get_rest_url( '/users' ),
				'redirect' => true,
				'fields'   => $fields,

				'button'   => [
					'label' => esc_html__( 'Register', 'hivepress' ),
				],
			],
			$args
		);

		parent::init( $args );
	}
}
