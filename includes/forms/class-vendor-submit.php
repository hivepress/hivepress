<?php
/**
 * Vendor submit form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Submits a new vendor.
 */
class Vendor_Submit extends Vendor_Update {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [],
			],
			$args
		);

		parent::__construct( $args );
	}
}
