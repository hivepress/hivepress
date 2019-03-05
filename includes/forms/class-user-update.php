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
class User_Update extends Model_Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = merge_arrays(
			$args,
			[
				'model'  => 'user',
				'fields' => [
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
			]
		);

		parent::__construct( $args );
	}
}
