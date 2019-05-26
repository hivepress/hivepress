<?php
/**
 * User delete form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User delete form class.
 *
 * @class User_Delete
 */
class User_Delete extends Model_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Form description.
	 *
	 * @var string
	 */
	protected static $description;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected static $model;

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'description' => esc_html__( 'Please enter your password below to permanently delete your account.', 'hivepress' ),
				'model'       => 'user',

				'fields'      => [
					'password' => [
						'required' => true,
						'order'    => 10,
					],
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
				'action'   => hp\get_rest_url( '/users/%id%' ),
				'method'   => 'DELETE',
				'redirect' => true,

				'button'   => [
					'label' => esc_html__( 'Delete Account', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
