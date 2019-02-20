<?php
/**
 * User request password form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User request password form class.
 *
 * @class User_Request_Password
 */
class User_Request_Password extends Form {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->fields = [
			'username' => [
				'label'      => esc_html__( 'Username or Email', 'hivepress' ),
				'type'       => 'text',
				'max_length' => 254,
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
