<?php
/**
 * User update profile form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Updates user profile.
 */
class User_Update_Profile extends User_Update {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'message'  => null,
				'redirect' => true,

				'fields'   => [
					'email'            => [
						'display_type' => 'hidden',
					],

					'password'         => [
						'display_type' => 'hidden',
					],

					'current_password' => [
						'display_type' => 'hidden',
					],

					// @todo remove temporary fix.
					'_form'            => [
						'type'      => 'hidden',
						'default'   => 'user_update_profile',
						'_separate' => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
