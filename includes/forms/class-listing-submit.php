<?php
/**
 * Listing submit form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing submit form class.
 *
 * @class Listing_Submit
 */
class Listing_Submit extends Form {

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
		$this->title = esc_html__( 'Submit Listing', 'hivepress' );
	}
}
