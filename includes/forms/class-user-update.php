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
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'user',
			],
			$meta
		);

		parent::init( $meta );
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
					'image'            => [
						'_order' => 10,
					],

					'first_name'       => [
						'_order' => 20,
					],

					'last_name'        => [
						'_order' => 30,
					],

					'description'      => [
						'_order' => 200,
					],

					'email'            => [
						'_order' => 300,
					],

					'password'         => [
						'label'      => esc_html__( 'New Password', 'hivepress' ),
						'_order'     => 310,

						'attributes' => [
							'autocomplete' => 'new-password',
						],
					],

					'current_password' => [
						'label'      => esc_html__( 'Current Password', 'hivepress' ),
						'type'       => 'password',
						'_separate'  => true,
						'_order'     => 320,

						'attributes' => [
							'autocomplete' => 'current-password',
						],
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
