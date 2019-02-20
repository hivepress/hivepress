<?php
/**
 * Listing update form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing update form class.
 *
 * @class Listing_Update
 */
class Listing_Update extends Form {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->fields = [
			'title'       => [
				'label'      => esc_html__( 'Title', 'hivepress' ),
				'type'       => 'text',
				'max_length' => 128,
				'required'   => true,
				'order'      => 10,
			],

			'description' => [
				'label'      => esc_html__( 'Description', 'hivepress' ),
				'type'       => 'textarea',
				'max_length' => 10240,
				'required'   => true,
				'order'      => 20,
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
