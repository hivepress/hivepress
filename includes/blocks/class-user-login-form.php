<?php
/**
 * User login form block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders the user login form.
 */
class User_Login_Form extends Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => esc_html__( 'User Login Form', 'hivepress' ),
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'form'       => 'user_login',

				'attributes' => [
					'class' => [ 'hp-form--narrow', 'hp-block' ],
				],

				'footer'     => [
					'form_actions' => [
						'type'       => 'container',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-form__actions' ],
						],

						'blocks'     => [
							'user_register_link'         => [
								'type'   => 'part',
								'path'   => 'user/login/user-register-link',
								'_order' => 10,
							],

							'user_password_request_link' => [
								'type'   => 'part',
								'path'   => 'user/login/user-password-request-link',
								'_order' => 20,
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( ! is_user_logged_in() || hivepress()->editor->is_preview() ) {
			$output .= parent::render();
		}

		return $output;
	}
}
