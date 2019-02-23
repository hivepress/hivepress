<?php
/**
 * User delete form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User delete form class.
 *
 * @class User_Delete
 */
class User_Delete extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		parent::__construct( $args );

		// Set fields.
		$this->set_fields(
			[
				'password' => [
					'label'    => esc_html__( 'Password', 'hivepress' ),
					'type'     => 'password',
					'required' => true,
					'order'    => 10,
				],
			]
		);
	}

	/**
	 * Submits form.
	 */
	public function submit() {
		parent::submit();

		if ( is_user_logged_in() ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';

			// Check password.
			if ( ! wp_check_password( $this->get_value( 'password' ), wp_get_current_user()->user_pass, get_current_user_id() ) ) {
				$this->errors[] = esc_html__( 'Password is incorrect.', 'hivepress' );
			} elseif ( ! current_user_can( 'manage_options' ) ) {

				// Delete user.
				wp_delete_user( get_current_user_id() );
			}
		}

		return empty( $this->errors );
	}
}
