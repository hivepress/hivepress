<?php
/**
 * Listing sort form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing sort form class.
 *
 * @class Listing_Sort
 */
class Listing_Sort extends Form {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->fields = [
			'sort' => [
				'label'   => esc_html__( 'Sort by', 'hivepress' ),
				'type'    => 'select',
				'options' => [],
				'order'   => 10,
			],
		];

		parent::__construct();
	}

	/**
	 * Submits form.
	 */
	public function submit() {
		parent::submit();

	}
}
