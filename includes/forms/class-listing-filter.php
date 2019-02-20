<?php
/**
 * Listing filter form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing filter form class.
 *
 * @class Listing_Filter
 */
class Listing_Filter extends Form {

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
	 *
	 * @param array $values Field values.
	 */
	public function submit( $values ) {

	}
}
