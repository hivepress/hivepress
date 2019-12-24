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
				'description' => esc_html__( 'Please enter your password below to permanently delete your account.', 'hivepress' ),
				'method'      => 'DELETE',
				'redirect'    => true,

				'fields'      => [
					'password' => [
						'min_length' => null,
						'required'   => true,
						'_order'     => 10,
					],
				],

				'button'      => [
					'label' => esc_html__( 'Delete Account', 'hivepress' ),
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
				'user_delete_action',
				[
					'user_id' => $this->model->get_id(),
				]
			);
		}

		parent::boot();
	}
}
