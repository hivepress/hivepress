<?php
/**
 * Listing delete form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing delete form class.
 *
 * @class Listing_Delete
 */
class Listing_Delete extends Form {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->fields = [

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
