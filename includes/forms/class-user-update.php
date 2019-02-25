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
		$args = array_replace_recursive(
			[
				'fields' => [
					'image'            => [
						'label'        => esc_html__( 'Profile Image', 'hivepress' ),
						'caption'      => esc_html__( 'Select Image', 'hivepress' ),
						'type'         => 'file_upload',
						'file_formats' => [ 'jpg', 'jpeg', 'png' ],
						'order'        => 10,
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
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
