<?php
/**
 * User reset password form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User reset password form class.
 *
 * @class User_Reset_Password
 */
class User_Reset_Password extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = array_replace_recursive(
			[
				'fields' => [
					'password' => [
						'label'      => esc_html__( 'New Password', 'hivepress' ),
						'type'       => 'password',
						'min_length' => 6,
						'required'   => true,
						'order'      => 10,
					],

					'username' => [
						'type'     => 'hidden',
						'required' => true,
					],

					'key'      => [
						'type'     => 'hidden',
						'required' => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
