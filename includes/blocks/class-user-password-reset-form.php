<?php
/**
 * User password reset form block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders the password reset form.
 */
class User_Password_Reset_Form extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'form'       => 'user_password_reset',

				'attributes' => [
					'class' => [ 'hp-form--narrow' ],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Set values.
		$this->values = array_merge(
			$this->values,
			[
				'username'           => hp\get_array_value( $_GET, 'username' ),
				'password_reset_key' => hp\get_array_value( $_GET, 'password_reset_key' ),
			]
		);

		parent::boot();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( ! is_wp_error( check_password_reset_key( $this->values['password_reset_key'], $this->values['username'] ) ) ) {
			$output .= parent::render();
		} else {
			$output .= ( new Part( [ 'path' => 'user/password-reset/user-password-reset-message' ] ) )->render();
		}

		return $output;
	}
}
