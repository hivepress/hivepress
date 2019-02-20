<?php
/**
 * Listing search form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing search form class.
 *
 * @class Listing_Search
 */
class Listing_Search extends Form {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->fields = [
			's' => [
				'placeholder' => esc_html__( 'Keywords', 'hivepress' ),
				'type'        => 'search',
				'max_length'  => 256,
				'order'       => 10,
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
