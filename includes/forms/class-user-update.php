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
class User_Update extends Form {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->fields = [
			'first_name'  => [
				'label'      => esc_html__( 'First Name', 'hivepress' ),
				'type'       => 'text',
				'max_length' => 64,
				'order'      => 20,
			],

			'last_name'   => [
				'label'      => esc_html__( 'Last Name', 'hivepress' ),
				'type'       => 'text',
				'max_length' => 64,
				'order'      => 30,
			],

			'description' => [
				'label'      => esc_html__( 'Profile Info', 'hivepress' ),
				'type'       => 'textarea',
				'max_length' => 2048,
				'order'      => 40,
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
update
