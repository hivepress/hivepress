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
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'model' => 'user',
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'message' => esc_html__( 'Your settings have been updated', 'hivepress' ),
				'action'  => hp\get_rest_url( '/users/%id%' ),

				'fields'  => [
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
						'label' => esc_html__( 'New Password', 'hivepress' ),
						'order' => 60,
					],

					'current_password' => [
						'label' => esc_html__( 'Current Password', 'hivepress' ),
						'type'  => 'password',
						'order' => 70,
					],
				],

				'button'  => [
					'label' => esc_html__( 'Update Settings', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
