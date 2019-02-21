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
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		parent::__construct( $args );
	}

	/**
	 * Submits form.
	 */
	public function submit() {
		parent::submit();
	}
}
