<?php
/**
 * User update form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User register form class.
 *
 * @class User_Update
 */
class User_Update extends Form {

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
				'image'            => [
					'label'      => esc_html__( 'Profile Image', 'hivepress' ),
					'caption'    => esc_html__( 'Select Image', 'hivepress' ),
					'type'       => 'file_upload',
					'extensions' => [ 'jpg', 'jpeg', 'png' ],
					'order'      => 10,
				],

				'first_name'       => [
					'label'      => esc_html__( 'First Name', 'hivepress' ),
					'type'       => 'text',
					'max_length' => 64,
					'order'      => 20,
				],

				'last_name'        => [
					'label'      => esc_html__( 'Last Name', 'hivepress' ),
					'type'       => 'text',
					'max_length' => 64,
					'order'      => 30,
				],

				'description'      => [
					'label'      => esc_html__( 'Profile Info', 'hivepress' ),
					'type'       => 'textarea',
					'max_length' => 2048,
					'order'      => 40,
				],

				'email'            => [
					'label'    => esc_html__( 'Email', 'hivepress' ),
					'type'     => 'email',
					'required' => true,
					'order'    => 50,
				],

				'new_password'     => [
					'label'      => esc_html__( 'New Password', 'hivepress' ),
					'type'       => 'password',
					'min_length' => 6,
					'order'      => 60,
				],

				'current_password' => [
					'label' => esc_html__( 'Current Password', 'hivepress' ),
					'type'  => 'password',
					'order' => 70,
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

			// Get user.
			$user = wp_get_current_user();

			// Get user name.
			$first_name   = hp_get_array_value( $values, 'first_name', '' );
			$last_name    = hp_get_array_value( $values, 'last_name', '' );
			$display_name = trim( $first_name . ' ' . $last_name );

			// Update name and description.
			update_user_meta( $user->ID, 'first_name', $first_name );
			update_user_meta( $user->ID, 'last_name', $last_name );
			update_user_meta( $user->ID, 'description', hp_get_array_value( $values, 'description' ) );

			if ( '' !== $display_name ) {
				wp_update_user(
					[
						'ID'           => $user->ID,
						'display_name' => $display_name,
					]
				);
			}

			// Update email and password.
			if ( $this->get_value( 'email' ) !== $user->user_email || '' !== $this->get_value( 'new_password' ) ) {

				// Check password.
				if ( '' === $this->get_value( 'current_password' ) ) {
					$this->errors[] = esc_html__( 'The current password is required.', 'hivepress' );
				} elseif ( ! wp_check_password( $this->get_value( 'current_password' ), $user->user_pass, $user->ID ) ) {
					$this->errors[] = esc_html__( 'The current password is incorrect.', 'hivepress' );
				} else {

					// Update email.
					if ( $this->get_value( 'email' ) !== $user->user_email ) {
						wp_update_user(
							[
								'ID'         => $user->ID,
								'user_email' => $this->get_value( 'email' ),
							]
						);
					}

					// Change password.
					if ( '' !== $this->get_value( 'new_password' ) ) {
						wp_update_user(
							[
								'ID'        => $user->ID,
								'user_pass' => $this->get_value( 'new_password' ),
							]
						);
					}
				}
			}
		}

		return empty( $this->errors );
	}
}
