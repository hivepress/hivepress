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
	 * Form message.
	 *
	 * @var string
	 */
	protected static $message;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected static $model;

	/**
	 * Form method.
	 *
	 * @var string
	 */
	protected static $method = 'POST';

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Form button.
	 *
	 * @var object
	 */
	protected static $button;

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'message' => esc_html__( 'Your settings have been updated', 'hivepress' ),
				'model'   => 'user',

				'fields'  => [
					'image_id'         => [
						'order' => 10,
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
				'action' => hp\get_rest_url( '/users/%id%' ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
