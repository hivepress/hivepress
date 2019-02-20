<?php
/**
 * Listing report form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing report form class.
 *
 * @class Listing_Report
 */
class Listing_Report extends Form {

	/**
	 * Form captcha.
	 *
	 * @var bool
	 */
	protected $captcha = false;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Set title.
		$this->title = esc_html__( 'Report Listing', 'hivepress' );

		// Set fields.
		$this->fields = [
			'reason' => [
				'type'       => 'textarea',
				'max_length' => 2048,
				'required'   => true,
				'order'      => 10,
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
