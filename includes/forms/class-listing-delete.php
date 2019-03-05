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
class Listing_Delete extends Model_Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp_merge_arrays(
			$args,
			[
				'model'  => 'listing',
				'method' => 'DELETE',
			]
		);

		parent::__construct( $args );
	}
}
