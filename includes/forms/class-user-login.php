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
	 */
	public function __construct() {

		// todo.
		$fields = [
			'username' => [
				'label'      => esc_html__( 'Username or Email', 'hivepress' ),
				'type'       => 'text',
				'max_length' => 254,
				'required'   => true,
				'order'      => 10,
			],

			'password' => [
				'name'     => esc_html__( 'Password', 'hivepress' ),
				'type'     => 'password',
				'required' => true,
				'order'    => 20,
			],
		];

		foreach ( $fields as $field_id => $field_args ) {
			$field_class               = '\HivePress\Fields\\' . $field_args['type'];
			$this->fields[ $field_id ] = new $field_class( $field_args );
		}
	}

	// todo.
	public function submit( $values ) {

		// Set credentials.
		$credentials = [
			'user_password' => $values['password'],
			'remember'      => true,
		];

		if ( is_email( $values['username'] ) ) {
			$credentials['user_email'] = $values['username'];
		} else {
			$credentials['user_login'] = $values['username'];
		}

		// Authenticate user.
		$user = wp_signon( $credentials, is_ssl() );
	}
}
