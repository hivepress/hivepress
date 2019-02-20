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
	 */
	public function __construct() {
		$this->fields = [
			'first_name'  => [
				'label'      => esc_html__( 'First Name', 'hivepress' ),
				'type'       => 'text',
				'max_length' => 64,
				'order'      => 20,
			],

			'last_name'   => [
				'label'      => esc_html__( 'Last Name', 'hivepress' ),
				'type'       => 'text',
				'max_length' => 64,
				'order'      => 30,
			],

			'description' => [
				'label'      => esc_html__( 'Profile Info', 'hivepress' ),
				'type'       => 'textarea',
				'max_length' => 2048,
				'order'      => 40,
			],
		];

		parent::__construct();
	}

	/**
	 * Submits form.
	 *
	 * @param array $values Field values.
	 */
	public function submit( $values ) {

		// Get user ID.
		$user_id = get_current_user_id();

		// Get user name.
		$first_name   = hp_get_array_value( $values, 'first_name', '' );
		$last_name    = hp_get_array_value( $values, 'last_name', '' );
		$display_name = trim( $first_name . ' ' . $last_name );

		// Update name and description.
		update_user_meta( $user_id, 'first_name', $first_name );
		update_user_meta( $user_id, 'last_name', $last_name );
		update_user_meta( $user_id, 'description', hp_get_array_value( $values, 'description' ) );

		if ( '' !== $display_name ) {
			wp_update_user(
				[
					'ID'           => $user_id,
					'display_name' => $display_name,
				]
			);
		}
	}
}
