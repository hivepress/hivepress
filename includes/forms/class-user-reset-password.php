reset<?php
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
	 */
	public function __construct() {
		$this->fields = [
			'password' => [
				'label'      => esc_html__( 'New Password', 'hivepress' ),
				'type'       => 'password',
				'min_length' => 6,
				'required'   => true,
				'order'      => 10,
			],
		];

		parent::__construct();
	}

	/**
	 * Submits form.
	 *
	 * @param array $values Field values.
	 */
	public function submit( $values ) {

	}
}
