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
 * User login form block class.
 *
 * @class User_Login_Form
 */
class User_Login_Form extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'form'   => 'user_login',

				'footer' => [
					'form_actions' => [
						'type'       => 'container',
						'order'      => 10,

						'attributes' => [
							'class' => [ 'hp-form__actions' ],
						],

						'blocks'     => [
							'user_register_link'         => [
								'type'     => 'element',
								'filepath' => 'user/login/user-register-link',
								'order'    => 10,
							],

							'user_password_request_link' => [
								'type'     => 'element',
								'filepath' => 'user/login/user-password-request-link',
								'order'    => 20,
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
	 * Bootstraps block properties.
	 */
	protected function bootstrap() {

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-form--narrow' ],
			]
		);

		parent::bootstrap();
	}
}
