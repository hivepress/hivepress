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
	 * Form meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'meta' => [
					'model' => 'user',
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
				'message' => esc_html__( 'Changes have been saved.', 'hivepress' ),

				'fields'  => [
					'image_id'         => [
						'_order' => 10,
					],

					'first_name'       => [
						'_order' => 20,
					],

					'last_name'        => [
						'_order' => 30,
					],

					'description'      => [
						'_order' => 40,
					],

					'email'            => [
						'_order' => 50,
					],

					'password'         => [
						'label'  => esc_html__( 'New Password', 'hivepress' ),
						'_order' => 60,
					],

					'current_password' => [
						'label'     => esc_html__( 'Current Password', 'hivepress' ),
						'type'      => 'password',
						'_excluded' => true,
						'_order'    => 70,
					],
				],

				'button'  => [
					'label' => esc_html__( 'Save Changes', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function boot() {

		// Set action.
		if ( $this->model->get_id() ) {
			$this->action = hivepress()->router->get_url(
				'user_update_action',
				[
					'user_id' => $this->model->get_id(),
				]
			);
		}

		parent::boot();
	}
}
