<?php
/**
 * User update form.
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
 * @class User_Update
 */
class User_Update extends Model_Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'model'  => 'user',
				'fields' => [
					'image_id'         => [
						'label'        => esc_html__( 'Profile Image', 'hivepress' ),
						'caption'      => esc_html__( 'Select Image', 'hivepress' ),
						'type'         => 'attachment_upload',
						'file_formats' => [ 'jpg', 'jpeg', 'png' ],
						'order'        => 10,
					],

					'first_name'       => [
						'order' => 20,
					],

					'last_name'        => [
						'order' => 30,
					],

					'description'      => [
						'order' => 40,
					],

					'email'            => [
						'order' => 50,
					],

					'password'         => [
						'label'    => esc_html__( 'New Password', 'hivepress' ),
						'required' => false,
						'order'    => 60,
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
